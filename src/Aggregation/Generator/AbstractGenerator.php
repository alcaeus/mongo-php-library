<?php

namespace MongoDB\Aggregation\Generator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\TypeGenerator;
use MongoDB\Aggregation\Expression\ResolvesToExpression;
use MongoDB\Aggregation\Expression\ResolvesToArrayExpression;
use MongoDB\Aggregation\Expression\ResolvesToMatchExpression;
use MongoDB\Aggregation\Expression\ResolvesToQuery;
use MongoDB\Aggregation\Expression\ResolvesToSortSpecification;
use function dirname;
use function file_exists;
use function file_put_contents;
use function mkdir;

/** @internal */
abstract class AbstractGenerator
{
    /** @var array[] */
    protected $typeAliases = [
        'resolvesToExpression' => [ResolvesToExpression::class, 'array', 'object', 'string', 'int', 'float', 'bool', 'null'],
        'resolvesToArrayExpression' => [ResolvesToArrayExpression::class, 'array', 'object', 'string'],
        'resolvesToBoolExpression' => [ResolvesToBoolExpression::class, 'array', 'object', 'string', 'bool'],
        'resolvesToMatchExpression' => ['array', 'object', ResolvesToMatchExpression::class],
        'resolvesToNumberExpression' => [ResolvesToBoolExpression::class, 'array', 'object', 'string', 'int', 'float'],
        'resolvesToQueryOperator' => ['array', 'object', ResolvesToQuery::class],
        'resolvesToSortSpecification' => ['array', 'object', ResolvesToSortSpecification::class],
    ];

    /** @var array */
    protected $interfaces;

    /** @var string */
    protected $filePath;

    /** @var string */
    protected $namespace;

    /** @var string|null */
    protected $parentClass;

    /** @var string */
    protected $classNameSuffix;

    public function __construct(array $generatorConfig)
    {
        $this->filePath = $generatorConfig['filePath'];
        $this->namespace = rtrim($generatorConfig['namespace'], '\\');
        $this->parentClass = $generatorConfig['parentClass'] ?? null;
        $this->interfaces = $generatorConfig['interfaces'] ?? [];
        $this->classNameSuffix = $generatorConfig['classNameSuffix'] ?? '';
    }

    public function createClassesForObjects(array $objects, bool $overwrite = false): void
    {
        array_map(
            function ($object) use ($overwrite) {
                $this->createFileForClass(
                    $this->filePath,
                    $this->createClassForObject($object),
                    $overwrite
                );
            },
            $objects
        );
    }

    abstract public function createClassForObject(object $object): ClassGenerator;

    final protected function generateTypeString(object $arg): string
    {
        $type = $arg->type;
        if ($arg->isOptional ?? false) {
            $type .= '|null';
        }

        return TypeGenerator::fromTypeString($this->resolveTypeAliases($type))->generate();
    }

    final protected function resolveTypeAliases(string $type): string
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

    protected function getClassName(object $object): string
    {
        return ucfirst($object->name) . $this->classNameSuffix;
    }

    protected function createFileForClass(string $filePath, ClassGenerator $classGenerator, bool $overwrite): void
    {
        $fileName = $classGenerator->getName() . '.php';
        $fullName = $filePath . '/' . $fileName;

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);

        $this->writeFileFromGenerator($fullName, $fileGenerator, $overwrite);
    }

    protected function writeFileFromGenerator(string $fullName, FileGenerator $fileGenerator, bool $overwrite): void
    {
        $filePath = dirname($fullName);

        if (file_exists($fullName) && !$overwrite) {
            return;
        }

        if ($overwrite) {
            $fileGenerator->setDocBlock(new DocBlockGenerator('THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!'));
        }

        if (!file_exists($filePath)) {
            mkdir($filePath, 0775, true);
        }

        file_put_contents($fullName, $fileGenerator->generate());
    }
}
