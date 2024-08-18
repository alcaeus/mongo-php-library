<?php

namespace MongoDB\PHPBSON;

use MongoDB\BSON\RegexInterface;
use function addslashes;
use function preg_quote;

final class Regex implements RegexInterface, Type
{
    final public function __construct(
        public readonly string $pattern,
        public readonly string $flags = '',
    ) {
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function getPattern()
    {
        return $this->pattern;
    }

    public function __toString()
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
