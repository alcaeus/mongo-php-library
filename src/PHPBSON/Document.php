<?php

namespace MongoDB\PHPBSON;

use MongoDB\BSON\Document as BSONDocument;
use MongoDB\PHPBSON\Index\DocumentIndex;
use MongoDB\PHPBSON\Index\Field;
use function addslashes;
use function array_map;
use function implode;
use function sprintf;

final class Document extends Structure
{
    static public function fromBSON(string $bson): Document
    {
        return new self($bson);
    }

    static public function fromJSON(string $json): Document
    {
        // TODO: Implement JSON parser
        return new self((string) BSONDocument::fromJSON($json));
    }

    static public function fromPHP(array|object $value): Document
    {
        // TODO: Create from PHP
        return new self((string) BSONDocument::fromPHP($value));
    }

    public function get(string $key): mixed
    {
        return $this->getIndex()->getFieldValue($key);
    }

    public function has(string $key): bool
    {
        return $this->getIndex()->hasField($key);
    }

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf(
            '{%s}',
            implode(
                ', ',
                array_map(
                    fn(Field $field): string => sprintf(
                        '"%s" : %s',
                        addslashes($field->key),
                        $this->formatValueForJson($field->getValue()),
                    ),
                    $this->getIndex()->fields,
                ),
            ),
        );
    }

    protected function createIndex(): DocumentIndex
    {
        return new DocumentIndex($this, (new Indexer())->getIndex($this->bson));
    }
}
