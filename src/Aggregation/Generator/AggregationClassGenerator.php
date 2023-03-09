<?php

namespace MongoDB\Aggregation\Generator;

use Laminas\Code\Generator\ClassGenerator;
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

    /** * @var string|null */
    private $parentClass;

    /** @var array[] */
    private $typeAliases = [
        'resolvesToExpression' => [ResolvesToExpression::class, 'array', 'object', 'string', 'int', 'float', 'bool', 'null'],
        'resolvesToArrayExpression' => [ResolvesToArrayExpression::class, 'array', 'object', 'string'],
        'resolvesToBoolExpression' => [ResolvesToBoolExpression::class, 'array', 'object', 'string', 'bool'],
        'resolvesToMatchExpression' => ['array', 'object', ResolvesToMatchExpression::class],
        'resolvesToNumberExpression' => [ResolvesToBoolExpression::class, 'array', 'object', 'string', 'int', 'float'],
        'resolvesToSortSpecification' => ['array', 'object', ResolvesToSortSpecification::class],
    ];

    public function __construct(string $filePath, string $namespace, ?string $parentClass)
    {
        $this->filePath = $filePath;
        $this->namespace = $namespace;
        $this->parentClass = $parentClass;
    }

    public function createClassForObject(object $object, bool $overwrite = false): void
    {
        $className = ucfirst($object->name);
        $fileName = $className . '.php';

        if (file_exists($this->filePath . $fileName) && !$overwrite) {
            return;
        }

        $classGenerator = new ClassGenerator($className, $this->namespace, ClassGenerator::FLAG_FINAL, $this->parentClass);
        $constructorGenerator = new MethodGenerator('__construct');
        $classGenerator->addMethods([$constructorGenerator]);

        $body = [];

        foreach ($object->args as $arg) {
            $classGenerator
                ->addPropertyFromGenerator($this->createArgProperty($arg))
                ->addMethodFromGenerator($this->createArgGetter($arg));
            $constructorGenerator->setParameter(new ParameterGenerator(
                $arg->name,
                $this->createTypeAlias($arg->type),
                $arg->defaultValue ?? null
            ));

            $body[] = sprintf('$this->%1$s = $%1$s;', $arg->name);
        }

        $constructorGenerator->setBody(implode(PHP_EOL, $body));

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

    private function createTypeAlias(string $type): string
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

    private function createArgGetter(object $arg): MethodGenerator
    {
        return (new MethodGenerator('get' . ucfirst($arg->name)))
            ->setBody(sprintf('return $this->%1$s;', $arg->name))
            ->setReturnType($this->createTypeAlias($arg->type));
    }

    private function createArgProperty(object $arg): PropertyGenerator
    {
        return new PropertyGenerator(
            $arg->name,
            $arg->defaultValue ?? null,
            PropertyGenerator::FLAG_PRIVATE,
            TypeGenerator::fromTypeString($this->createTypeAlias($arg->type))
        );
    }
}
