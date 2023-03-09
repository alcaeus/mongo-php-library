<?php

namespace MongoDB\Aggregation\Generator;

use Laminas\Code\Generator\ClassGenerator;
use Laminas\Code\Generator\DocBlock\Tag\GenericTag;
use Laminas\Code\Generator\DocBlockGenerator;
use Laminas\Code\Generator\MethodGenerator;
use Laminas\Code\Generator\ParameterGenerator;
use Laminas\Code\Generator\PropertyGenerator;
use function ucfirst;
use const PHP_EOL;

/** @internal */
final class AggregationValueHolderGenerator extends AbstractGenerator
{
    public function createClassForObject(object $object): ClassGenerator
    {
        $className = $this->getClassName($object);

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

        return $classGenerator;
    }

    private function createArgGetter(object $arg): MethodGenerator
    {
        return (new MethodGenerator('get' . ucfirst($arg->name)))
            ->setBody(sprintf('return $this->%1$s;', $arg->name))
            ->setDocBlock(
                (new DocBlockGenerator())
                    ->setTag(new GenericTag('return', $this->generateTypeString($arg)))
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
                    ->setTag(new GenericTag('var', $this->generateTypeString($arg) . ' $' . $arg->name))
            )
            ->omitDefaultValue();
    }

    private function createConstructorParameter($arg): ParameterGenerator
    {
        $isOptional = $arg->isOptional ?? false;

        return (new ParameterGenerator($arg->name))
            ->setDefaultValue($arg->defaultValue ?? null)
            ->omitDefaultValue(!$isOptional);
    }

    private function createConstructorDocblock(array $args): DocBlockGenerator
    {
        $tags = array_map(
            function (object $arg): GenericTag {
                return new GenericTag('param', $this->generateTypeString($arg) . ' $' . $arg->name);
            },
            $args
        );

        return new DocBlockGenerator(null, null, $tags);
    }
}
