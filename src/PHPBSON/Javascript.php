<?php

namespace MongoDB\PHPBSON;

use MongoDB\BSON\JavascriptInterface;
use function addslashes;

class Javascript implements Type, JavascriptInterface
{
    public function __construct(
        public readonly string $code,
        public readonly Document|null $scope = null,
    ) {}

    public function getCode(): string
    {
        return $this->code;
    }

    public function getScope(): Document|null
    {
        return $this->scope;
    }

    public function __toString(): string
    {
        return $this->code;
    }

    public function toCanonicalExtendedJSON(): string
    {
        return $this->scope !== null
            ? sprintf('{"$code" : "%s", "$scope" : %s}', addslashes($this->code), $this->scope->toCanonicalExtendedJSON())
            : sprintf('{"$code" : "%s"}', addslashes($this->code));
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
