<?php

namespace MongoDB\PHPBSON\Index;

use InvalidArgumentException;
use MongoDB\BSON\Binary;
use MongoDB\BSON\Int64;
use MongoDB\BSON\Javascript;
use MongoDB\BSON\MaxKey;
use MongoDB\BSON\MinKey;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\UTCDateTime;
use MongoDB\PHPBSON\Document;
use MongoDB\PHPBSON\Type;
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
        Document $source,
        public readonly string $key,
        public readonly int $bsonType,
        public readonly int $keyOffset,
        public readonly int $keyLength,
        public readonly int|null $dataOffset = null,
        public readonly int|null $dataLength = null,
    ) {
        // TODO: throw if dataOffset or dataLength are null

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
        if (! $source instanceof Document) {
            // Make this a little easier to handle
            throw new OutOfBoundsException('BSON document is no longer valid');
        }

        $bson = (string) $source;
        $data = false;

        switch ($this->bsonType) {
            case Type::DOUBLE:
                $this->value = (float) $this->unpackWithChecks('edata', $bson, $this->dataOffset, 'data');
                break;

            case Type::STRING:
                $this->value = $this->unpackWithChecks('Z' . $this->dataLength . 'data', $bson, $this->dataOffset, 'data');
                break;

            case Type::CODE:
                $code = $this->unpackWithChecks('Z' . $this->dataLength . 'data', $bson, $this->dataOffset, 'data');
                $this->value = new Javascript($code);
                break;

            // TODO
//            case Type::SYMBOL:
//                break;

            case Type::DOCUMENT:
                // TODO: Handle PackedArray
                $this->value = Document::fromBSON(substr($bson, $this->dataOffset, $this->dataLength));
                break;

            // TODO
//            case Type::ARRAY:
//                break;

            case Type::BINARY:
                $data = $this->unpackWithChecks('Csubtype/c' . ($this->dataLength - 1) . 'data', $bson, $this->dataOffset);

                $this->value = new Binary($data['data'], (int) $data['subtype']);
                break;

            case Type::UNDEFINED:
                $this->value = unserialize('O:22:"MongoDB\BSON\Undefined":0:{}');
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
                $this->value = new UTCDateTime(substr($bson, $this->dataOffset, $this->dataLength));
                break;

            case Type::TIMESTAMP:
                $data = $this->unpackWithChecks('Vincrement/Vtimestamp', $bson, $this->dataOffset);

                $this->value = new Timestamp((int) $data['increment'], (int) $data['timestamp']);
                break;

            case Type::INT64:
                $this->value = new Int64(substr($bson, $this->dataOffset, $this->dataLength));
                break;

            case Type::REGEX:
                $pattern = $this->unpackWithChecks('Z*pattern', $bson, $this->dataOffset, 'pattern');
                $flags = $this->unpackWithChecks('Z*flags', $bson, $this->dataOffset + strlen($pattern) + 1, 'flags');

                $this->value = new Regex($pattern, $flags);
                break;

            // TODO
//            case Type::DBPOINTER:
//                // string (byte*12)
//                $data = @unpack('Vlength', $bson, $newOffset);
//                if ($data === false) {
//                    throw new InvalidArgumentException('Invalid BSON data');
//                }
//
//                $dataOffset = $newOffset;
//                // Data length includes 4 bytes for the string length and 12 bytes for an ObjectId
//                $dataLength = 4 + (int) $data['length'] + 12;
//                $newOffset += $dataLength + 12;
//                break;

            // TODO
//            case Type::CODEWITHSCOPE:
//                // int32 string document
//                // The int32 contains the total number of bytes in the code_w_scope
//                $data = @unpack('Vlength', $bson, $newOffset);
//                if ($data === false) {
//                    throw new InvalidArgumentException('Invalid BSON data');
//                }
//
//                // Skip the 4 byte length
//                $dataOffset = $newOffset + 4;
//                $dataLength = (int) $data['length'];
//                $newOffset = $dataOffset + $dataLength;
//                break;

            case Type::INT32:
                $this->value = (int) $this->unpackWithChecks('Vdata', $bson, $this->dataOffset, 'data');
                break;

            // TODO
//            case Type::DECIMAL128:
//                $dataLength = 16;
//                $dataOffset = $newOffset;
//                $newOffset += $dataLength;
//                break;

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
