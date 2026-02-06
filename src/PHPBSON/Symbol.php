<?php

namespace MongoDB\PHPBSON;

use function json_encode;
use function sprintf;

class Symbol implements Type
{
    public function __construct(
        public readonly string $symbol,
    ) {
    }

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf('{"$symbol": %s}', json_encode($this->symbol));
    }

    public function toRelaxedExtendedJSON(): string
    {
        return $this->toCanonicalExtendedJSON();
    }
}
