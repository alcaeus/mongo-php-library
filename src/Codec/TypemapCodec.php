<?php

namespace MongoDB\Codec;

use MongoDB\BSON\Document;

use MongoDB\BSON\Int64;
use MongoDB\BSON\PackedArray;
use MongoDB\BSON\Unserializable;
use MongoDB\Exception\UnsupportedValueException;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;

use ReflectionClass;
use function array_flip;
use function array_intersect_key;
use function array_keys;
use function array_map;
use function class_exists;
use function is_array;
use function is_object;
use function str_starts_with;
use function strpos;
use const PHP_INT_SIZE;

class TypemapCodec implements Codec
{
    use DecodeIfSupported;
    use EncodeIfSupported;

    private const DEFAULT_TYPEMAP = [
        'array' => BSONArray::class,
        'document' => BSONDocument::class,
        'root' => BSONDocument::class,
    ];

    private readonly string $rootType;
    private readonly string $documentType;
    private readonly string $arrayType;

    private readonly array $fieldPaths;

    public function __construct(?array $typeMap = null)
    {
        $typeMap = $typeMap ?? [];
        $typeMap += self::DEFAULT_TYPEMAP;

        $this->rootType = $typeMap['root'];
        $this->documentType = $typeMap['document'];
        $this->arrayType = $typeMap['array'];

        $this->fieldPaths = $typeMap['fieldPaths'] ?? [];
    }

    public function canDecode(mixed $value): bool
    {
        return $value instanceof Document || $value instanceof PackedArray;
    }

    public function canEncode(mixed $value): bool
    {
        return is_object($value) || is_array($value);
    }

    public function decode(mixed $value): mixed
    {
        if ($this->rootType === 'array') {
            return $this->decodeToArray($value);
        }

        if ($this->rootType === 'object') {
            return (object) $this->decodeToArray($value);
        }

        // If we get here, we expect this to be a class string
        if (! class_exists($this->rootType)) {
            // TODO: More specific exception
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        $reflectionClass = new ReflectionClass($this->rootType);
        if (! $reflectionClass->implementsInterface(Unserializable::class)) {
            // TODO: More specific exception
            throw UnsupportedValueException::invalidDecodableValue($value);
        }

        $object = $reflectionClass->newInstanceWithoutConstructor();
        $object->bsonUnserialize($this->decodeToArray($value));

        return $object;
    }

    public function encode(mixed $value): Document
    {
        // TODO: handle Serializable and Persistable

        return Document::fromPHP($value);
    }

    private function decodeToArray(Document|PackedArray $documentOrPackedArray): array
    {
        $result = [];

        foreach ($documentOrPackedArray as $key => $value) {
            $fieldPathPrefix = $documentOrPackedArray instanceof Document ? $key : '$';

            $result[$key] = match (true) {
                $value instanceof Document => $this->decodeEmbeddedBSON($fieldPathPrefix, $value, $this->documentType),
                $value instanceof PackedArray => $this->decodeEmbeddedBSON($fieldPathPrefix, $value, $this->arrayType),
                $value instanceof Int64 => PHP_INT_SIZE === 4 ? $value : (int) $value,
                default => $value,
            };
        }

        return $result;
    }

    private function decodeEmbeddedBSON(string $key, Document|PackedArray $documentOrPackedArray, string $fallbackType): mixed
    {
        $codec = new TypemapCodec([
            'root' => $this->getTypeForKey($key) ?? $fallbackType,
            'document' => $this->documentType,
            'array' => $this->arrayType,
            'fieldPaths' => $this->getFieldPathsForKey($key),
        ]);

        return $codec->decode($documentOrPackedArray);
    }

    private function getFieldPathsForKey(string $key): array
    {
        $matchingFieldPaths = [];

        $prefix = $key . '.';
        $prefixLength = strlen($prefix);

        foreach ($this->fieldPaths as $fieldPath => $type) {
            if (! str_starts_with($fieldPath, $prefix)) {
                continue;
            }

            $newFieldPath = substr($fieldPath, $prefixLength);

            $matchingFieldPaths[$newFieldPath] = $type;
        }

        return $matchingFieldPaths;
    }

    private function getTypeForKey(string $key): ?string
    {
        return $this->fieldPaths[$key] ?? null;
    }
}
