<?php

namespace MongoDB\Options;

use MongoDB\Exception\InvalidArgumentException;
use ReflectionClass;

use function str_starts_with;

/** @internal */
abstract class AbstractOptions
{
    final protected function __construct()
    {
    }

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public static function fromArray(array $options): self
    {
        $instance = new static();

        foreach ((new ReflectionClass(static::class))->getMethods() as $method) {
            if (! str_starts_with($method->getName(), 'extractAndValidate')) {
                continue;
            }

            $method->invoke($instance, $options);
        }

        return $instance;
    }
}
