<?php

namespace MongoDB\PHPBSON;

use ArrayAccess;
use Exception;
use InvalidArgumentException;
use MongoDB\PHPBSON\Index\Index;
use Stringable;

use function base64_decode;
use function base64_encode;
use function get_debug_type;
use function is_bool;
use function is_float;
use function is_infinite;
use function is_int;
use function is_nan;
use function is_null;
use function is_string;
use function json_encode;
use function sprintf;
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

    public function __debugInfo(): array
    {
        return [
            'data' => base64_encode($this->bson),
            'value' => $this->toCanonicalExtendedJSON(),
        ];
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

    abstract public function toCanonicalExtendedJSON(): string;

    abstract public function toRelaxedExtendedJSON(): string;

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

    protected function formatValueForCanonicalExtendedJson(mixed $value): string
    {
        return match (true) {
            $value instanceof Type => $value->toCanonicalExtendedJSON(),
            is_string($value) => json_encode($value),
            // TODO: Handle 64 bit values
            is_int($value) => sprintf('{"$numberInt": "%d"}', $value),
            is_float($value) => $this->formatFloat($value, true),
            is_bool($value) => $value ? 'true' : 'false',
            is_null($value) => 'null',
            default => throw new Exception('Unsupported field type ' . get_debug_type($value)),
        };
    }

    protected function formatValueForRelaxedExtendedJson(mixed $value): string
    {
        return match (true) {
            is_float($value) => $this->formatFloat($value, false),
            // TODO: Handle 64 bit values
            is_int($value) => sprintf('%d', $value),
            $value instanceof Type => $value->toRelaxedExtendedJSON(),
            default => $this->formatValueForCanonicalExtendedJson($value),
        };
    }

    protected function getIndex(): Index
    {
        return $this->index ??= $this->createIndex();
    }

    private function formatFloat(float $value, bool $forceExtended): string
    {
        if (is_nan($value)) {
            $value = 'NaN';
            $forceExtended = true;
        } elseif (is_infinite($value)) {
            $value = ($value < 0 ? '-' : '') . 'Infinity';
            $forceExtended = true;
        } else {
            // TODO: Formatting of float values
            $value = sprintf('%.13f', $value);
        }

        return $forceExtended ?
            sprintf('{"$numberDouble": "%s"}', $value) :
            $value;
    }
}
