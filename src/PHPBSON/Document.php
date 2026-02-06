<?php

namespace MongoDB\PHPBSON;

use Closure;
use MongoDB\BSON\Document as BSONDocument;
use MongoDB\PHPBSON\Index\DocumentIndex;
use MongoDB\PHPBSON\Index\Field;

use function array_map;
use function implode;
use function json_encode;
use function sprintf;

final class Document extends Structure
{
    public static function fromBSON(string $bson): Document
    {
        return new self($bson);
    }

    public static function fromJSON(string $json): Document
    {
        // TODO: Implement JSON parser
        return new self((string) BSONDocument::fromJSON($json));
    }

    public static function fromPHP(array|object $value): Document
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
        return $this->toExtendedJSON($this->formatValueForCanonicalExtendedJson(...));
    }

    public function toRelaxedExtendedJSON(): string
    {
        return $this->toExtendedJSON($this->formatValueForRelaxedExtendedJson(...));
    }

    protected function createIndex(): DocumentIndex
    {
        return new DocumentIndex($this, (new Indexer())->getIndex($this->bson));
    }

    private function toExtendedJSON(Closure $formatter): string
    {
        return sprintf(
            '{%s}',
            implode(
                ', ',
                array_map(
                    fn (Field $field): string => sprintf(
                        '%s : %s',
                        json_encode($field->key),
                        $formatter($field->getValue()),
                    ),
                    $this->getIndex()->fields,
                ),
            ),
        );
    }
}
