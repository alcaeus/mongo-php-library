<?php

namespace MongoDB\PHPBSON;

interface Type
{
    public const DOUBLE = 1; // TODO: Create BSON type class
    public const STRING = 2; // TODO: Create BSON type class
    public const DOCUMENT = 3;
    public const ARRAY = 4;
    public const BINARY = 5;
    public const UNDEFINED = 6;
    public const OBJECTID = 7;
    public const BOOLEAN = 8; // TODO: Create BSON type class
    public const UTCDATETIME = 9;
    public const NULL = 10; // TODO: Create BSON type class
    public const REGEX = 11;
    public const DBPOINTER = 12;
    public const CODE = 13;
    public const SYMBOL = 14;
    public const CODEWITHSCOPE = 15;
    public const INT32 = 16; // TODO: Create BSON type class
    public const TIMESTAMP = 17;
    public const INT64 = 18;
    public const DECIMAL128 = 19;
    public const MINKEY = -1;
    public const MAXKEY = 127;

    public function toCanonicalExtendedJSON(): string;

    public function toRelaxedExtendedJSON(): string;
}
