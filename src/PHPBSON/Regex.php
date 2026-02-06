<?php

namespace MongoDB\PHPBSON;

use MongoDB\BSON\RegexInterface;

use function addslashes;
use function preg_quote;
use function sprintf;

final class Regex implements RegexInterface, Type
{
    final public function __construct(
        public readonly string $pattern,
        public readonly string $flags = '',
    ) {
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
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
