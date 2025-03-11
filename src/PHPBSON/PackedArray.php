<?php

namespace MongoDB\PHPBSON;

use Closure;
use MongoDB\BSON\Document as BSONDocument;
use MongoDB\BSON\PackedArray as BSONPackedArray;
use MongoDB\PHPBSON\Index\Field;
use MongoDB\PHPBSON\Index\PackedArrayIndex;
use function array_map;

final class PackedArray extends Structure
{
    static public function fromBSON(string $bson): PackedArray
    {
        return new self($bson);
    }

    static public function fromJSON(string $json): PackedArray
    {
        // TODO: Implement JSON parser
        return new self((string) BSONDocument::fromJSON($json));
    }

    static public function fromPHP(array|object $value): PackedArray
    {
        // TODO: Create from PHP
        return new self((string) BSONPackedArray::fromPHP($value));
    }

    public function get(int $key): mixed
    {
        return $this->getIndex()->getFieldValue($key);
    }

    public function has(string $key): bool
    {
        return $this->getIndex()->hasField($key);
    }

    protected function createIndex(): PackedArrayIndex
    {
        return new PackedArrayIndex($this, (new Indexer())->getIndex($this->bson));
    }

    public function toCanonicalExtendedJSON(): string
    {
        return $this->toExtendedJSON($this->formatValueForCanonicalExtendedJson(...));
    }

    public function toRelaxedExtendedJSON(): string
    {
        return $this->toExtendedJSON($this->formatValueForRelaxedExtendedJson(...));
    }

    private function toExtendedJSON(Closure $formatter): string
    {
        return sprintf(
            '[%s]',
            implode(
                ', ',
                array_map(
                    fn (Field $field): string => $formatter($field->getValue()),
                    $this->getIndex()->fields,
                ),
            ),
        );
    }
}
