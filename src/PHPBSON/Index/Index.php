<?php

namespace MongoDB\PHPBSON\Index;

use MongoDB\PHPBSON\Structure;
use OutOfBoundsException;

abstract class Index
{
    public readonly array $fields;

    public function __construct(
        Structure $structure,
        /** @param list<array{key: string, bsonType: int, keyOffset: int, keyLength: int, dataOffset?: ?int, dataLength?: ?int}> $fields */
        array $fields,
    ) {
        $this->fields = array_map(
            fn(array $field): Field => new Field(
                $structure,
                $field['key'],
                $field['bsonType'],
                $field['keyOffset'],
                $field['keyLength'],
                $field['dataOffset'] ?? null,
                $field['dataLength'] ?? null,
            ),
            static::sortFields($fields),
        );
    }

    public function hasField(string $key): bool
    {
        return isset($this->fields[$key]);
    }

    public function getFieldValue(string $key): mixed
    {
        return $this->getField($key)->getValue();
    }

    public function getField(string $key): Field
    {
        return $this->fields[$key] ?? throw new OutOfBoundsException('Field "' . $key . '" not found in BSON document');
    }
}
