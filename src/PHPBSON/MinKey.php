<?php

namespace MongoDB\PHPBSON;

final class MinKey implements Type
{
    public function toCanonicalExtendedJSON(): string
    {
        return '{"$minKey" : 1}';
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
