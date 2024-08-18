<?php

namespace MongoDB\PHPBSON;

use function addslashes;

class Symbol implements Type
{
    public function __construct(
        public readonly string $symbol,
    ) {}

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf('{"$symbol": "%s"}', addslashes($this->symbol));
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
