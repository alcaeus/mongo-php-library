<?php

namespace MongoDB\PHPBSON\Index;

use InvalidArgumentException;
use MongoDB\PHPBSON\Binary;
use MongoDB\PHPBSON\DBPointer;
use MongoDB\PHPBSON\Decimal128;
use MongoDB\PHPBSON\Document;
use MongoDB\PHPBSON\Int64;
use MongoDB\PHPBSON\Javascript;
use MongoDB\PHPBSON\MaxKey;
use MongoDB\PHPBSON\MinKey;
use MongoDB\PHPBSON\ObjectId;
use MongoDB\PHPBSON\PackedArray;
use MongoDB\PHPBSON\Regex;
use MongoDB\PHPBSON\Structure;
use MongoDB\PHPBSON\Symbol;
use MongoDB\PHPBSON\Timestamp;
use MongoDB\PHPBSON\Type;
use MongoDB\PHPBSON\Undefined;
use MongoDB\PHPBSON\UTCDateTime;
use OutOfBoundsException;
use WeakReference;
use function bin2hex;
use function strlen;
use function substr;
use function unpack;

final class Field
{
    private readonly WeakReference $source;
    private bool $isInitialized = false;
    private mixed $value;

    public function __construct(
        Structure $source,
        public readonly string $key,
        public readonly int $bsonType,
        public readonly int $keyOffset,
        public readonly int $keyLength,
        public readonly int|null $dataOffset = null,
        public readonly int|null $dataLength = null,
    ) {
        if ($this->bsonType === Type::UNDEFINED
            || $this->bsonType === Type::NULL
            || $this->bsonType === Type::MINKEY
            || $this->bsonType === Type::MAXKEY) {
            if ($this->dataLength !== 0 || $this->dataOffset !== null) {
                throw new InvalidArgumentException('Invalid data offset or length');
            }
        } else {
            if ($this->dataLength === null || $this->dataOffset === null) {
                throw new InvalidArgumentException('Invalid data offset or length');
            }

            if ($this->dataLength < 0 || $this->dataOffset <= $this->keyOffset + $this->keyLength) {
                throw new InvalidArgumentException('Invalid data offset or length');
            }
        }

        $this->source = WeakReference::create($source);
    }

    public function getValue(): mixed
    {
        if (! $this->isInitialized) {
            $this->readValue();
        }

        return $this->value;
    }

    private function readValue(): void
    {
        $source = $this->source->get();
        if (! $source instanceof Structure) {
            // Make this a little easier to handle
            throw new OutOfBoundsException('BSON document is no longer valid');
        }

        $bson = (string) $source;

        switch ($this->bsonType) {
            case Type::DOUBLE:
                $this->value = (float) $this->unpackWithChecks('edata', $bson, $this->dataOffset, 'data');
                break;

            case Type::STRING:
                $this->value = $this->unpackWithChecks('a' . $this->dataLength . 'data', $bson, $this->dataOffset, 'data');
                break;

            case Type::CODE:
                $code = $this->unpackWithChecks('a' . $this->dataLength . 'data', $bson, $this->dataOffset, 'data');
                $this->value = new Javascript($code);
                break;

            case Type::CODEWITHSCOPE:
                $codeLength = (int) $this->unpackWithChecks('Vlength', $bson, $this->dataOffset, 'length');

                // $codeLength includes the trailing NUL byte - make sure we exclude that when reading data
                $code = $this->unpackWithChecks('a' . ($codeLength - 1) . 'data', $bson, $this->dataOffset + 4, 'data');
                $scope = Document::fromBSON(substr($bson, $this->dataOffset + 4 + $codeLength, $this->dataLength - $codeLength - 4));

                // TODO: Scope may not properly handle BSON documents
                $this->value = new Javascript($code, $scope);
                break;

            case Type::SYMBOL:
                // @todo: Unpack this?
                $this->value = new Symbol(substr($bson, $this->dataOffset, $this->dataLength));
                break;

            case Type::DOCUMENT:
                $this->value = Document::fromBSON(substr($bson, $this->dataOffset, $this->dataLength));
                break;

            case Type::ARRAY:
                $this->value = PackedArray::fromBSON(substr($bson, $this->dataOffset, $this->dataLength));
                break;

            case Type::BINARY:
                $data = $this->unpackWithChecks('Csubtype/a' . ($this->dataLength - 1) . 'data', $bson, $this->dataOffset);

                /* subtype 2 has a redundant length header in the data */
                if ((int) $data['subtype'] === Binary::TYPE_OLD_BINARY) {
                    $data['data'] = substr($data['data'], 4);
                }

                $this->value = new Binary($data['data'], (int) $data['subtype']);
                break;

            case Type::UNDEFINED:
                $this->value = new Undefined();
                break;

            case Type::NULL:
                $this->value = null;
                break;

            case Type::MINKEY:
                $this->value = new MinKey();
                break;

            case Type::MAXKEY:
                $this->value = new MaxKey();
                break;

            case Type::OBJECTID:
                $this->value = new ObjectId(bin2hex(substr($bson, $this->dataOffset, $this->dataLength)));
                break;

            case Type::BOOLEAN:
                $this->value = (bool) $this->unpackWithChecks('Cdata', $bson, $this->dataOffset, 'data');
                break;

            case Type::UTCDATETIME:
                // TODO: q is machine byte order, needs little endian
                // TODO: causes issues on 32-bit systems
                $timestamp = $this->unpackWithChecks('qdata', $bson, $this->dataOffset, 'data');
                $this->value = new UTCDateTime($timestamp);
                break;

            case Type::TIMESTAMP:
                $data = $this->unpackWithChecks('Vincrement/Vtimestamp', $bson, $this->dataOffset);

                $this->value = new Timestamp((int) $data['increment'], (int) $data['timestamp']);
                break;

            case Type::INT64:
                // TODO: q is machine byte order, needs little endian
                // TODO: causes issues on 32-bit systems
                $value = $this->unpackWithChecks('qdata', $bson, $this->dataOffset, 'data');
                $this->value = new Int64($value);
                break;

            case Type::REGEX:
                $pattern = $this->unpackWithChecks('Z*pattern', $bson, $this->dataOffset, 'pattern');
                $flags = $this->unpackWithChecks('Z*flags', $bson, $this->dataOffset + strlen($pattern) + 1, 'flags');

                $this->value = new Regex($pattern, $flags);
                break;

            case Type::DBPOINTER:
                $refLength = (int) $this->unpackWithChecks('Vlength', $bson, $this->dataOffset, 'length');

                $data = $this->unpackWithChecks('Z' . $refLength . 'ref/Z12id', $bson, $this->dataOffset + 4);
                $this->value = new DBPointer($data['ref'], bin2hex($data['id']));
                break;

            case Type::INT32:
                // TODO: 'l' is machine byte order, should be little endian always
                $this->value = (int) $this->unpackWithChecks('ldata', $bson, $this->dataOffset, 'data');
                break;

            case Type::DECIMAL128:
                // Create Decimal128 from 16-byte little-endian binary slice
                $this->value = new Decimal128(substr($bson, $this->dataOffset, $this->dataLength));
                break;

            default:
                throw new InvalidArgumentException('Invalid BSON type ' . $this->bsonType);
        }

        $this->isInitialized = true;
    }

    private function unpackWithChecks(string $format, string $string, int $offset = 0, ?string $key = null): mixed
    {
        $data = @unpack($format, $string, $offset);
        if ($data === false) {
            throw new InvalidArgumentException('Invalid BSON data');
        }

        if ($key !== null) {
            return $data[$key];
        }

        return $data;
    }
}
