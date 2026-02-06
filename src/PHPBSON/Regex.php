<?php

namespace MongoDB\PHPBSON;

use MongoDB\BSON\RegexInterface;

use function addslashes;
use function implode;
use function preg_quote;
use function sort;
use function sprintf;
use function str_split;

final class Regex implements RegexInterface, Type
{
    public readonly string $flags;

    final public function __construct(
        public readonly string $pattern,
        string $flags = '',
    ) {
        $this->flags = $this->sortFlags($flags);
    }

    public function getFlags(): string
    {
        return $this->flags;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function __toString(): string
    {
        return sprintf('/%s/%s', preg_quote($this->pattern, '/'), $this->flags);
    }

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf('{"$regularExpression" : { "pattern": "%s", "options" : "%s"}}', addslashes($this->pattern), addslashes($this->flags));
    }

    public function toRelaxedExtendedJSON(): string
    {
        return $this->toCanonicalExtendedJSON();
    }

    private function sortFlags(string $flags): string
    {
        $flagArray = str_split($flags);
        sort($flagArray);

        return implode('', $flagArray);
    }
}
