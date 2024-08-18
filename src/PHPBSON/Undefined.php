<?php

namespace MongoDB\PHPBSON;

final class Undefined implements Type
{
    public function toCanonicalExtendedJSON(): string
    {
        return '{"$undefined" : true}';
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
