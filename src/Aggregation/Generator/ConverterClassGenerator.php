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
use function array_map;
use function array_merge;
use function array_unique;
use function file_put_contents;
use function sprintf;
use function ucfirst;
use const PHP_EOL;

/** @internal */
final class ConverterClassGenerator extends AbstractGenerator
{
    public function createClassForObject(object $object, bool $overwrite = false): void
    {
        $className = $object->className ?? ucfirst($object->name);
        $fileName = $className . '.php';

        if (file_exists($this->filePath . $fileName) && !$overwrite) {
            return;
        }

        $classGenerator = new ClassGenerator($className, $this->namespace, ClassGenerator::FLAG_FINAL, $this->parentClass, $this->interfaces);
        $supportsGenerator = (new MethodGenerator('supports'))
            ->setDocBlock(new DocBlockGenerator(null, null, [new GenericTag('param', 'mixed $value')]))
            ->setParameter(new ParameterGenerator('value'))
            ->setReturnType('bool')
            ->setBody(
                <<<PHP
                    return false;
PHP
            );

        $convertGenerator = (new MethodGenerator('convert'))
            ->setDocBlock(new DocBlockGenerator(null, null, [new GenericTag('param', 'mixed $value')]))
            ->setParameter(new ParameterGenerator('value'))
            ->setBody(
                $this->createConvertBody($object)
            );

        $classGenerator->addMethods([$supportsGenerator, $convertGenerator]);

        $fileGenerator = new FileGenerator();
        $fileGenerator->setClass($classGenerator);

        if (! file_exists($this->filePath)) {
            mkdir($this->filePath, 0777, true);
        }

        file_put_contents($this->filePath . $fileName, $fileGenerator->generate());
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
}
