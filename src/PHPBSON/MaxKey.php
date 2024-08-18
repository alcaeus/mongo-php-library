<?php

namespace MongoDB\PHPBSON;

final class MaxKey implements Type
{
    public function toCanonicalExtendedJSON(): string
    {
        return '{"$maxKey" : 1}';
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
