<?php

namespace MongoDB\Aggregation\Generator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use MongoDB\Aggregation\Expression\ResolvesToExpression;
use MongoDB\Aggregation\Expression\ResolvesToArrayExpression;
use MongoDB\Aggregation\Expression\ResolvesToMatchExpression;
use MongoDB\Aggregation\Expression\ResolvesToSortSpecification;
use MongoDB\Codec\CodecLibrary;
use function array_map;
use function array_merge;
use function implode;
use function rtrim;
use function sprintf;
use function ucfirst;

/** @internal */
final class ConverterClassGenerator extends AbstractGenerator
{
    /** @var string */
    private $supportingNamespace;

    /** @var string */
    private $supportingClassNameSuffix;

    /** @var string */
    private $libraryNamespace;

    /** @var string|null */
    private $libraryClassName;

    public function __construct(array $generatorConfig)
    {
        parent::__construct($generatorConfig);

        if (!isset($generatorConfig['supportingNamespace'])) {
            throw new InvalidArgumentException('Required parameter "supportingNamespace" missing');
        }

        $this->supportingNamespace = rtrim($generatorConfig['supportingNamespace'], '\\');
        $this->supportingClassNameSuffix = $generatorConfig['supportingClassNameSuffix'] ?? '';
        $this->libraryNamespace = rtrim($generatorConfig['libraryNamespace'] ?? '', '\\');
        $this->libraryClassName = $generatorConfig['libraryClassName'] ?? null;
    }

    public function createClassesForObjects(array $objects, bool $overwrite = false): void
    {
        parent::createClassesForObjects($objects, $overwrite);

        if ($this->libraryNamespace) {
            $this->createNamespaceConverter($objects);
        }
    }

    public function createClassForObject(object $object): ClassGenerator
    {
        $className = $this->getClassName($object);

        $classGenerator = new ClassGenerator($className, $this->namespace, ClassGenerator::FLAG_FINAL, $this->parentClass, $this->interfaces);
        $supportsGenerator = (new MethodGenerator('supports'))
            ->setDocBlock(new DocBlockGenerator(null, null, [new GenericTag('param', 'mixed $value')]))
            ->setParameter(new ParameterGenerator('value'))
            ->setReturnType('bool')
            ->setBody($this->createSupportsBody($object));

        $convertGenerator = (new MethodGenerator('convert'))
            ->setDocBlock(new DocBlockGenerator(null, null, [new GenericTag('param', 'mixed $value')]))
            ->setParameter(new ParameterGenerator('value'))
            ->setBody($this->createConvertBody($object));

        $classGenerator->addMethods([$supportsGenerator, $convertGenerator]);

        return $classGenerator;
    }

    private function createNamespaceConverter(array $objects): void
    {
        $filePath = dirname(rtrim($this->filePath, '/'));

        // No separation between namespace intentionally.
        $className = $this->libraryClassName ?? $this->namespace . $this->classNameSuffix;

        $classGenerator = new ClassGenerator($className, $this->libraryNamespace, ClassGenerator::FLAG_FINAL, CodecLibrary::class);
        $constructorGenerator = (new MethodGenerator('__construct'))
            ->setBody(sprintf('parent::__construct(%s);', implode(', ', $this->createConverterList($objects))));

        $classGenerator->addMethodFromGenerator($constructorGenerator);

        $this->createFileForClass($filePath, $classGenerator, true);
    }

    private function createConverterList(array $objects): array
    {
        return array_map(
            function (object $object): string
            {
                return sprintf('new \\%s()', $this->namespace . '\\' . $this->getClassName($object));
            },
            $objects
        );
    }

    private function createConvertBody(object $object): string
    {
        $args = array_merge(...array_map(
            function (object $arg): array
            {
                return [[
                    'name' => $arg->name,
                    'value' => sprintf(
                        '$this->encodeWithLibraryIfSupported($value->%s())',
                        'get' . ucfirst($arg->name)
                    )
                ]];
            },
            $object->args
        ));

        $format = <<<'PHP'
            return (object) [
                '$%1$s' => %2$s
            ];
PHP;

        $usesNamedArgs = $object->usesNamedArgs ?? false;
        if (count($args) == 1 && !$usesNamedArgs) {
            $argumentString = $args[0]['value'];
        } elseif ($usesNamedArgs) {
            $argSpecs = array_map(
                function (array $argSpec): string {
                    return sprintf("'%1\$s' => %2\$s,", $argSpec['name'], $argSpec['value']);
                },
                $args
            );

            $argumentString = sprintf("(object) [\n%s\n]", implode("\n", $argSpecs));
        } else {
            $argSpecs = array_map(
                function (array $argSpec): string {
                    return sprintf('%s,', $argSpec['value']);
                },
                $args
            );

            $argumentString = sprintf("[\n%s\n]", implode("\n", $argSpecs));
        }

        return sprintf($format, $object->name, $argumentString);
    }

    private function createSupportsBody(object $object): string
    {
        return sprintf('return $value instanceof %s::class;', $this->getSupportingClassName($object));
    }

    private function getSupportingClassName($object): ?string
    {
        return $this->supportingNamespace . '\\' . ucfirst($object->name) . $this->supportingClassNameSuffix;
    }
}
