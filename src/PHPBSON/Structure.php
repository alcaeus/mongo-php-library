<?php

namespace MongoDB\PHPBSON;

use ArrayAccess;
use Exception;
use InvalidArgumentException;
use MongoDB\PHPBSON\Index\Index;
use Stringable;

use function base64_decode;
use function base64_encode;
use function strlen;
use function substr;
use function unpack;

/** @internal */
abstract class Structure implements ArrayAccess, Stringable, Type
{
    protected Index|null $index = null;

    abstract protected function createIndex(): Index;

    protected function __construct(protected string $bson)
    {
        $this->validate($bson);
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

    public function getIterator(): Iterator
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

    protected function getIndex(): Index
    {
        return $this->index ??= $this->createIndex();
    }
}
