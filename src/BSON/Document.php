<?php

namespace MongoDB\BSON;

use ArrayAccess;
use Exception;
use InvalidArgumentException;
use Stringable;

use function base64_encode;
use function strlen;
use function substr;

final class Document implements ArrayAccess, Stringable
{
    private string $bson;

    private function __construct(string $bson)
    {
        $this->validate($bson);
        $this->bson = $bson;
    }

    static public function fromBSON(string $bson): Document
    {
        return new self($bson);
    }

    static public function fromJSON(string $json): Document
    {
        throw new Exception('Not implemented');
    }

    static public function fromPHP(array|object $value): Document
    {
        throw new Exception('Not implemented');
    }

    public function get(string $key): mixed
    {
        throw new Exception('Not implemented');
    }

    public function getIterator(): Iterator
    {
        throw new Exception('Not implemented');
    }

    public function has(string $key): bool
    {
        throw new Exception('Not implemented');
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
        throw new Exception('Not implemented');
    }

    public function offsetGet(mixed $offset): mixed
    {
        throw new Exception('Not implemented');
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
        $data = @unpack('L', $bson);
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
}
