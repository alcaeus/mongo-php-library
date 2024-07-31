<?php

namespace MongoDB\PHPBSON\Index;

use MongoDB\PHPBSON\Document;
use OutOfBoundsException;
use function array_column;
use function array_map;

final readonly class DocumentIndex
{
    public readonly array $fields;

    public function __construct(
        Document $document,
        /** @param list<array{key: string, bsonType: int, keyOffset: int, keyLength: int, dataOffset?: ?int, dataLength?: ?int}> $fields */
        array $fields,
    ) {
        $this->fields = array_map(
            fn (array $field): Field => new Field(
                $document,
                $field['key'],
                $field['bsonType'],
                $field['keyOffset'],
                $field['keyLength'],
                $field['dataOffset'] ?? null,
                $field['dataLength'] ?? null,
            ),
            array_column($fields, null, 'key'),
        );
    }

    public function hasField(string $key): bool
    {
        return isset($this->fields[$key]);
    }

    public function getField(string $key): Field
    {
        return $this->fields[$key] ?? throw new OutOfBoundsException('Field "' . $key . '" not found in BSON document');
    }

    public function getFieldValue(string $key): mixed
    {
        return $this->getField($key)->getValue();
    }
}
