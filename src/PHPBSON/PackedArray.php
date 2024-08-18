<?php

namespace MongoDB\PHPBSON;

use MongoDB\BSON\Document as BSONDocument;
use MongoDB\BSON\PackedArray as BSONPackedArray;
use MongoDB\PHPBSON\Index\PackedArrayIndex;

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
}
