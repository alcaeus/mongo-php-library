<?php

namespace MongoDB\Aggregation\Generator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use Laminas\Code\Generator\TypeGenerator;
use MongoDB\Aggregation\Expression\ResolvesToMatchExpression;
use MongoDB\Aggregation\Expression\ResolvesToSortSpecification;
use MongoDB\Aggregation\Stage;
use function array_merge;
use function array_unique;
use function file_put_contents;
use function ucfirst;
use const PHP_EOL;

class StageGenerator
{
    private $typeAliases = [
        'resolvesToMatchExpression' => ['array', 'object', ResolvesToMatchExpression::class],
        'resolvesToSortSpecification' => ['array', 'object', ResolvesToSortSpecification::class],
    ];

    public function createClassForStage(object $stage, bool $overwrite = false): void
    {
        $filePath = __DIR__ . '/../Stage/';
        $className = ucfirst($stage->name);
        $fileName = $className . '.php';

        if (file_exists($filePath . $fileName) && !$overwrite) {
            return;
        }

        $classGenerator = new ClassGenerator($className, Stage::class, ClassGenerator::FLAG_FINAL, Stage::class);
        $constructorGenerator = new MethodGenerator('__construct');
        $classGenerator->addMethods([$constructorGenerator]);

        $body = [];

        foreach ($stage->args as $arg) {
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

        file_put_contents($filePath . $fileName, $fileGenerator->generate());
    }

    public function createClassesForStages(array $stages, bool $overwrite = false): void
    {
        array_map(
            function ($stage) use ($overwrite) {
                $this->createClassForStage($stage, $overwrite);
            },
            $stages
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

    private function createArgGetter($arg): MethodGenerator
    {
        return (new MethodGenerator('get' . ucfirst($arg->name)))
            ->setBody(sprintf('return $this->%1$s;', $arg->name))
            ->setReturnType($this->createTypeAlias($arg->type));
    }

    private function createArgProperty($arg): PropertyGenerator
    {
        return new PropertyGenerator(
            $arg->name,
            $arg->defaultValue ?? null,
            PropertyGenerator::FLAG_PRIVATE,
            TypeGenerator::fromTypeString($this->createTypeAlias($arg->type))
        );
    }
}
