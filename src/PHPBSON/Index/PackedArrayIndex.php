<?php

namespace MongoDB\PHPBSON\Index;

final class PackedArrayIndex extends Index
{
    protected static function sortFields(array $fields): array
    {
        return $fields;
    }
}
