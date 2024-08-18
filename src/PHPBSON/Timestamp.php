<?php

namespace MongoDB\PHPBSON;

use MongoDB\BSON\TimestampInterface;
use function sprintf;

final class Timestamp implements TimestampInterface, Type
{
    final public function __construct(
        public readonly int $increment,
        public readonly int $timestamp,
    ) {}

    public function getIncrement()
    {
        return $this->increment;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function __toString()
    {
        // TODO: Implement __toString() method.
    }

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf('{"$timestamp" : {"t" : %d, "i" : %d} }', $this->timestamp, $this->increment);
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
