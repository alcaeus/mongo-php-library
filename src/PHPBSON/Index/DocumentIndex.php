<?php

namespace MongoDB\PHPBSON\Index;

use function array_column;

final class DocumentIndex extends Index
{
    protected static function sortFields(array $fields): array
    {
        return array_column($fields, null, 'key');
    }
}
