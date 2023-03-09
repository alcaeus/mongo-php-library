<?php

namespace MongoDB\Aggregation\Generator;

use Laminas\Code\Generator\TypeGenerator;

abstract class AbstractGenerator
{
    /** @var array[] */
    protected $typeAliases = [
        'resolvesToExpression' => [ResolvesToExpression::class, 'array', 'object', 'string', 'int', 'float', 'bool', 'null'],
        'resolvesToArrayExpression' => [ResolvesToArrayExpression::class, 'array', 'object', 'string'],
        'resolvesToBoolExpression' => [ResolvesToBoolExpression::class, 'array', 'object', 'string', 'bool'],
        'resolvesToMatchExpression' => ['array', 'object', ResolvesToMatchExpression::class],
        'resolvesToNumberExpression' => [ResolvesToBoolExpression::class, 'array', 'object', 'string', 'int', 'float'],
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

    // TODO: Having this final is ugly but required
    final public function __construct(string $filePath, string $namespace, ?string $parentClass, array $interfaces)
    {
        $this->filePath = $filePath;
        $this->namespace = $namespace;
        $this->parentClass = $parentClass;
        $this->interfaces = $interfaces;
    }

    final public function createClassesForObjects(array $objects, bool $overwrite = false): void
    {
        array_map(
            function ($object) use ($overwrite) {
                $this->createClassForObject($object, $overwrite);
            },
            $objects
        );
    }

    abstract public function createClassForObject(object $object, bool $overwrite = false): void;

    final protected function generateTypeString(string $type): string
    {
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
}
