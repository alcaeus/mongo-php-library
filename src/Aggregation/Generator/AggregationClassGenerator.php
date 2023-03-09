<?php

namespace MongoDB\Aggregation\Generator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use MongoDB\Aggregation\Expression\ResolvesToExpression;
use MongoDB\Aggregation\Expression\ResolvesToArrayExpression;
use MongoDB\Aggregation\Expression\ResolvesToMatchExpression;
use MongoDB\Aggregation\Expression\ResolvesToSortSpecification;
use function array_merge;
use function array_unique;
use function file_put_contents;
use function ucfirst;
use const PHP_EOL;

/** @internal */
final class AggregationClassGenerator
{
    /** @var string */
    private $filePath;

    /** @var string */
    private $namespace;

    /** @var string|null */
    private $parentClass;

    /** @var array */
    private $interfaces;

    /** @var array[] */
    private $typeAliases = [
        'resolvesToExpression' => [ResolvesToExpression::class, 'array', 'object', 'string', 'int', 'float', 'bool', 'null'],
        'resolvesToArrayExpression' => [ResolvesToArrayExpression::class, 'array', 'object', 'string'],
        'resolvesToBoolExpression' => [ResolvesToBoolExpression::class, 'array', 'object', 'string', 'bool'],
        'resolvesToMatchExpression' => ['array', 'object', ResolvesToMatchExpression::class],
        'resolvesToNumberExpression' => [ResolvesToBoolExpression::class, 'array', 'object', 'string', 'int', 'float'],
        'resolvesToSortSpecification' => ['array', 'object', ResolvesToSortSpecification::class],
    ];

    public function __construct(string $filePath, string $namespace, ?string $parentClass, array $interfaces)
    {
        $this->filePath = $filePath;
        $this->namespace = $namespace;
        $this->parentClass = $parentClass;
        $this->interfaces = $interfaces;
    }

    public function createClassForObject(object $object, bool $overwrite = false): void
    {
        $className = $object->className ?? ucfirst($object->name);
        $fileName = $className . '.php';

        if (file_exists($this->filePath . $fileName) && !$overwrite) {
            return;
        }

        $classGenerator = new ClassGenerator($className, $this->namespace, ClassGenerator::FLAG_FINAL, $this->parentClass, $this->interfaces);
        $constructorGenerator = new MethodGenerator('__construct');
        $classGenerator->addMethods([$constructorGenerator]);

        $body = [];

        foreach ($object->args as $arg) {
            $classGenerator
                ->addPropertyFromGenerator($this->createArgProperty($arg))
                ->addMethodFromGenerator($this->createArgGetter($arg));
            $constructorGenerator->setParameter($this->createConstructorParameter($arg));

            $body[] = sprintf('$this->%1$s = $%1$s;', $arg->name);
        }

        $constructorGenerator->setBody(implode(PHP_EOL, $body));
        $constructorGenerator->setDocBlock($this->createConstructorDocblock($object->args));

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);

        if (! file_exists($this->filePath)) {
            mkdir($this->filePath, 0777, true);
        }

        file_put_contents($this->filePath . $fileName, $fileGenerator->generate());
    }

    public function createClassesForObjects(array $objects, bool $overwrite = false): void
    {
        array_map(
            function ($object) use ($overwrite) {
                $this->createClassForObject($object, $overwrite);
            },
            $objects
        );
    }

    private function createArgGetter(object $arg): MethodGenerator
    {
        return (new MethodGenerator('get' . ucfirst($arg->name)))
            ->setBody(sprintf('return $this->%1$s;', $arg->name))
            ->setDocBlock(
                (new DocBlockGenerator())
                    ->setTag(new GenericTag('return', $this->generateTypeString($arg->type)))
            );
    }

    private function createArgProperty(object $arg): PropertyGenerator
    {
        return (new PropertyGenerator(
            $arg->name,
            $arg->defaultValue ?? null,
            PropertyGenerator::FLAG_PRIVATE
        ))
            ->setDocBlock(
                (new DocBlockGenerator())
                    ->setTag(new GenericTag('var', $this->generateTypeString($arg->type) . ' $' . $arg->name))
            );
    }

    private function createConstructorParameter($arg): ParameterGenerator
    {
        return new ParameterGenerator($arg->name, null, $arg->defaultValue ?? null);
    }

    private function createConstructorDocblock(array $args): DocBlockGenerator
    {
        $tags = array_map(
            function (object $arg): GenericTag {
                return new GenericTag('param', $this->generateTypeString($arg->type) . ' $' . $arg->name);
            },
            $args
        );

        return new DocBlockGenerator(null, null, $tags);
    }

    private function generateTypeString(string $type): string
    {
        return TypeGenerator::fromTypeString($this->resolveTypeAliases($type))->generate();
    }

    private function resolveTypeAliases(string $type): string
    {
        return implode(
            '|',
            array_unique(
                array_merge(...array_map(
                    function ($type): array {
                        return $this->typeAliases[$type] ?? [$type];
                    },
                    explode('|', $type)
                ))
            )
        );
    }
}
