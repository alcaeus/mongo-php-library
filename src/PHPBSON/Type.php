<?php

namespace MongoDB\PHPBSON;

interface Type
{
    const DOUBLE = 1;
    const STRING = 2;
    const DOCUMENT = 3;
    const ARRAY = 4;
    const BINARY = 5;
    const UNDEFINED = 6;
    const OBJECTID = 7;
    const BOOLEAN = 8;
    const UTCDATETIME = 9;
    const NULL = 10;
    const REGEX = 11;
    const DBPOINTER = 12;
    const CODE = 13;
    const SYMBOL = 14;
    const CODEWITHSCOPE = 15;
    const INT32 = 16;
    const TIMESTAMP = 17;
    const INT64 = 18;
    const DECIMAL128 = 19;
    const MINKEY = -1;
    const MAXKEY = 127;

    public function toCanonicalExtendedJSON(): string;

    public function toRelaxedExtendedJSON(): string;
}
