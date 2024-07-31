<?php

namespace MongoDB\PHPBSON;

use ArrayAccess;
use Exception;
use InvalidArgumentException;
use MongoDB\BSON\Document as BSONDocument;
use MongoDB\PHPBSON\Index\DocumentIndex;
use Stringable;

use function base64_encode;
use function strlen;
use function substr;

final class Document implements ArrayAccess, Stringable
{
    private DocumentIndex|null $index = null;

    private function __construct(private string $bson)
    {
        $this->validate($bson);
    }

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

    public function getIterator(): Iterator
    {
        throw new Exception('Not implemented');
    }

    public function has(string $key): bool
    {
        return $this->getIndex()->hasField($key);
    }

    public function toPHP(?array $typeMap = null): array|object
    {
        throw new Exception('Not implemented');
    }

    public function toCanonicalExtendedJSON(): string
    {
        throw new Exception('Not implemented');
    }

    public function toRelaxedExtendedJSON(): string
    {
        throw new Exception('Not implemented');
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->getIndex()->hasField($offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getIndex()->getFieldValue($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Exception('Not implemented');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new Exception('Not implemented');
    }

    public function __toString(): string
    {
        return $this->bson;
    }

    public function __serialize(): array
    {
        return ['data' => base64_encode($this->bson)];
    }

    public function __unserialize(array $data): void
    {
        $bson = base64_decode($data['data']);
        $this->validate($bson);
        $this->bson = $bson;
    }

    private function validate(string $bson): void
    {
        $data = @unpack('V', $bson);
        if ($data === false) {
            throw new InvalidArgumentException('Invalid BSON data');
        }

        [1 => $length] = $data;
        if ($length < 5) {
            throw new InvalidArgumentException('Invalid BSON length');
        }

        if (strlen($bson) !== $length) {
            throw new InvalidArgumentException('Invalid BSON length');
        }

        if (substr($bson, -1, 1) !== "\0") {
            throw new InvalidArgumentException('Invalid BSON length');
        }
    }

    private function getIndex(): DocumentIndex
    {
        return $this->index ??= new DocumentIndex($this, (new Indexer())->getIndex($this->bson));
    }
}
