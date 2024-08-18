<?php

namespace MongoDB\PHPBSON;

use MongoDB\BSON\BinaryInterface;

final class Binary implements BinaryInterface, Type
{
    public const TYPE_GENERIC = 0;
    public const TYPE_FUNCTION = 1;
    public const TYPE_OLD_BINARY = 2;
    public const TYPE_OLD_UUID = 3;
    public const TYPE_UUID = 4;
    public const TYPE_MD5 = 5;

    /**
     * @since 1.7.0
     */
    public const TYPE_ENCRYPTED = 6;

    /**
     * @since 1.12.0
     */
    public const TYPE_COLUMN = 7;
    public const TYPE_USER_DEFINED = 128;

    final public function __construct(
        public readonly string $data,
        public readonly int $type = self::TYPE_GENERIC,
    ) {}

    final public function getData(): string
    {
        return $this->data;
    }

    final public function getType(): int
    {
        return $this->type;
    }

    public function __toString(): string
    {
        return $this->data;
    }

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf('{ "$binary" : {"base64" : "%s", "subType" : "%s"}}', base64_encode($this->data), $this->type);
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
