<?php

namespace MongoDB\Aggregation\Generator;

use InvalidArgumentException;
use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Reflection\MethodReflection;
use function array_filter;
use function array_map;
use function array_merge;
use function file_exists;
use function implode;
use function in_array;
use function lcfirst;
use function rtrim;
use function sprintf;
use function str_replace;
use function ucfirst;

/** @internal */
final class FactoryClassGenerator extends AbstractGenerator
{
    private const RESERVED_NAMES = ['abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'finally', 'fn', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'match', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'readonly', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor', 'yield',];

    /** @var string */
    private $className;

    /** @var string */
    private $supportingNamespace;

    /** @var string */
    private $supportingClassNameSuffix;

    /** @var string */
    private $functionFilePath;

    /** @var string */
    private $functionNamespace;

    public function __construct(array $generatorConfig)
    {
        parent::__construct($generatorConfig);

        if (!isset($generatorConfig['className'])) {
            throw new InvalidArgumentException(('Required parameter "className" missing'));
        }

        if (!isset($generatorConfig['supportingNamespace'])) {
            throw new InvalidArgumentException('Required parameter "supportingNamespace" missing');
        }

        $this->className = $generatorConfig['className'];
        $this->supportingNamespace = rtrim($generatorConfig['supportingNamespace'], '\\');
        $this->supportingClassNameSuffix = $generatorConfig['supportingClassNameSuffix'] ?? '';
        $this->functionFilePath = $generatorConfig['functionFilePath'] ?? '';
        $this->functionNamespace = $generatorConfig['functionNamespace'] ?? '';
    }

    public function createClassesForObjects(array $objects, bool $overwrite = false): void
    {
        $this->createFileForClass(
            $this->filePath,
            $this->createFactoryClass($objects),
            $overwrite
        );

        $this->createFactoryFunctionFile($objects, $overwrite);
    }

    protected function getClassName(?object $object = null): string
    {
        return parent::getClassName($object ?? (object) ['name' => $this->className]);
    }

    private function createFactoryFunctionFile(array $objects, bool $overwrite)
    {
        if (!$this->functionFilePath || !$this->functionNamespace) {
            return;
        }

        $fileName = $this->getClassName() . '.php';
        $fullName = $this->functionFilePath . '/' . $fileName;

        $fileBody = <<<'PHP'
namespace %1$s;

%2$s
PHP;

        $fileGenerator = new FileGenerator();
        $builderMethodsCode = array_map(
            function (MethodGenerator $generator): string {
                $functionName = $generator->getName();
                return str_replace(
                    'public function ' . $functionName,
                    'function ' . $functionName,
                    $generator->generate()
                );
            },
            $this->getBuilderMethods($objects, false)
        );
        $fileGenerator
            ->setBody(sprintf(
                $fileBody,
                $this->functionNamespace,
                implode("\n\n", $builderMethodsCode)
            ));

        $this->writeFileFromGenerator($fullName, $fileGenerator, $overwrite);
    }

    private function createFactoryClass(array $objects): ClassGenerator
    {
        $className = $this->getClassName();

        $classGenerator = new ClassGenerator($className, $this->namespace);
        $classGenerator->addMethods($this->getBuilderMethods($objects, true));

        return $classGenerator;
    }

    public function createClassForObject(object $object): ClassGenerator
    {
        $className = $this->getClassName($object);

        $classGenerator = new ClassGenerator($className, $this->namespace, ClassGenerator::FLAG_FINAL, $this->parentClass, $this->interfaces);
        $supportsGenerator = (new MethodGenerator('supports'))
            ->setVisibility(MethodGenerator::VISIBILITY_PROTECTED)
            ->setDocBlock(new DocBlockGenerator(null, null, [new GenericTag('param', 'mixed $expression')]))
            ->setParameter(new ParameterGenerator('expression'))
            ->setReturnType('bool')
            ->setBody($this->createSupportsBody($object));

        $convertGenerator = (new MethodGenerator('convert'))
            ->setVisibility(MethodGenerator::VISIBILITY_PROTECTED)
            ->setDocBlock(new DocBlockGenerator(null, null, [new GenericTag('param', 'mixed $expression')]))
            ->setParameter(new ParameterGenerator('expression'))
            ->setBody($this->createConvertBody($object));

        $classGenerator->addMethods([$supportsGenerator, $convertGenerator]);

        return $classGenerator;
    }

    private function createConvertBody(object $object): string
    {
        $args = array_merge(...array_map(
            function (object $arg): array
            {
                $format = $arg->isVariadic ?? false
                    ? 'array_map([$this, \'encodeWithLibraryIfSupported\'], $expression->%s())'
                    : '$this->encodeWithLibraryIfSupported($expression->%s())';

                return [[
                    'name' => $arg->name,
                    'value' => sprintf($format, 'get' . ucfirst($arg->name))
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

            $argumentFormat = "(object) [\n%s\n]";
            if ($this->hasOptionalArgs($object)) {
                $argumentFormat = <<<'PHP'
                    (object) array_filter(
                        [%%s],
                        function ($value, $key): bool
                        {
                            return !in_array($key, ['%s']) || $value !== null;
                        },
                        ARRAY_FILTER_USE_BOTH
                    )
PHP;
                $argumentFormat = sprintf($argumentFormat, implode("', '", $this->getOptionalArgNames($object)));
            }

            $argumentString = sprintf($argumentFormat, implode("\n", $argSpecs));
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
        return sprintf('return $expression instanceof \\%s;', $this->getSupportingClassName($object));
    }

    private function getSupportingClassName($object): ?string
    {
        return $this->supportingNamespace . '\\' . ucfirst($object->name) . $this->supportingClassNameSuffix;
    }

    private function hasOptionalArgs(object $object)
    {
        foreach ($object->args as $arg) {
            if ($arg->isOptional ?? false) {
                return true;
            }
        }

        return false;
    }

    private function getOptionalArgNames(object $object): array
    {
        return array_map(
            function (object $arg): string {
                return $arg->name;
            },
            array_filter(
                $object->args,
                function (object $arg): bool {
                    return $arg->isOptional ?? false;
                }
            )
        );
    }

    private function getBuilderMethods(array $objects, $forClass): array
    {
        return array_map(
            function (object $object) use ($forClass): MethodGenerator
            {
                $supportedFQCN = $this->supportingNamespace . '\\' . ucfirst($object->name) . $this->supportingClassNameSuffix;
                return MethodGenerator::fromReflection(new MethodReflection($supportedFQCN, '__construct'))
                    ->setName($this->getBuilderMethodName($object, $forClass))
                    ->setBody(sprintf('return new \\%s(...func_get_args());', $supportedFQCN))
                    ->setStatic($forClass)
                    ->setReturnType($supportedFQCN);
            },
            $objects
        );
    }

    function getBuilderMethodName($object, bool $forClass): string
    {
        $methodName = lcfirst($object->name);
        if ($this->isReservedName($object->name, $forClass)) {
            $methodName .= $this->supportingClassNameSuffix;
        }

        return $methodName;
    }

    private function isReservedName(string $name, bool $forClass): bool
    {
        return !$forClass && in_array($name, self::RESERVED_NAMES);
    }
}
