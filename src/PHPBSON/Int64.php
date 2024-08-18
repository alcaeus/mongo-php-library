<?php

namespace MongoDB\PHPBSON;

final class Int64 implements Type
{
    final public function __construct(public readonly string|int $value) {}

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf('{"$numberLong" : "%d"}', $this->value);
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
