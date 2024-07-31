<?php

namespace MongoDB\Tests\PHPBSON;

use Generator;
use MongoDB\PHPBSON\Indexer;
use MongoDB\PHPBSON\Type;
use MongoDB\Tests\TestCase;

use function hex2bin;
use function pack;

class IndexerTest extends TestCase
{
    /** @dataProvider provideBSONData */
    public function testGetIndex(array $expected, string $bson): void
    {
        $indexer = new Indexer();

        self::assertEquals($expected, $indexer->getIndex($bson));
    }

    public static function provideBSONData(): Generator
    {
        yield 'Empty document' => [
            'expected' => [],
            // {}
            'bson' => pack('Lx', 5),
        ];

        yield 'Double' => [
            'expected' => [[
                'key' => 'd',
                'bsonType' => Type::DOUBLE,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 8,
            ]],
            // {"d" : {"$numberDouble": "1.0"}}
            'bson' => hex2bin('10000000016400000000000000F03F00'),
        ];

        yield 'String' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::STRING,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 11,
                'dataLength' => 12,
            ]],
            // {"a" : "abababababab"}
            'bson' => hex2bin('190000000261000D0000006162616261626162616261620000'),
        ];

        yield 'String with longer key' => [
            'expected' => [[
                'key' => 'ab',
                'bsonType' => Type::STRING,
                'keyOffset' => 5,
                'keyLength' => 2,
                'dataOffset' => 12,
                'dataLength' => 12,
            ]],
            // {"ab" : "abababababab"}
            'bson' => hex2bin('1A000000026162000D0000006162616261626162616261620000'),
        ];

        yield 'Document' => [
            'expected' => [[
                'key' => 'x',
                'bsonType' => Type::DOCUMENT,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 14,
            ]],
            // {"x" : {"a" : "b"}}
            'bson' => hex2bin('160000000378000E0000000261000200000062000000'),
        ];

        yield 'Array' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::ARRAY,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 12,
            ]],
            // {"a" : [{"$numberInt": "10"}]}
            'bson' => hex2bin('140000000461000C0000001030000A0000000000'),
        ];

        yield 'Binary (Sub-type 0)' => [
            'expected' => [[
                'key' => 'x',
                'bsonType' => Type::BINARY,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 11,
                'dataLength' => 3,
            ]],
            // {"x" : { "$binary" : {"base64" : "//8=", "subType" : "00"}}}
            'bson' => hex2bin('0F0000000578000200000000FFFF00'),
        ];

        yield 'Undefined' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::UNDEFINED,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => null,
                'dataLength' => 0,
            ]],
            // {"a" : {"$undefined" : true}}
            'bson' => hex2bin('0800000006610000'),
        ];

        yield 'ObjectId' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::OBJECTID,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 12,
            ]],
            // {"a" : {"$oid" : "000000000000000000000000"}}
            'bson' => hex2bin('1400000007610000000000000000000000000000'),
        ];

        yield 'Boolean' => [
            'expected' => [[
                'key' => 'b',
                'bsonType' => Type::BOOLEAN,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 1,
            ]],
            // {"b" : true}
            'bson' => hex2bin('090000000862000100'),
        ];

        yield 'UTCDateTime' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::UTCDATETIME,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 8,
            ]],
            // {"a" : {"$date" : {"$numberLong" : "0"}}}
            'bson' => hex2bin('10000000096100000000000000000000'),
        ];

        yield 'Null' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::NULL,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => null,
                'dataLength' => 0,
            ]],
            // {"a" : null}
            'bson' => hex2bin('080000000A610000'),
        ];

        yield 'Regex' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::REGEX,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 7,
            ]],
            // {"a" : {"$regularExpression" : { "pattern": "abc", "options" : "im"}}}
            'bson' => hex2bin('0F0000000B610061626300696D0000'),
        ];

        yield 'DBPointer' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::DBPOINTER,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 18,
            ]],
            // {"a": {"$dbPointer": {"$ref": "b", "$id": {"$oid": "56e1fc72e0c917e9c4714161"}}}}
            'bson' => hex2bin('1A0000000C610002000000620056E1FC72E0C917E9C471416100'),
        ];

        yield 'Code' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::CODE,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 11,
                'dataLength' => 12,
            ]],
            // {"a" : {"$code" : "abababababab"}}
            'bson' => hex2bin('190000000D61000D0000006162616261626162616261620000'),
        ];

        yield 'Symbol' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::SYMBOL,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 11,
                'dataLength' => 12,
            ]],
            // {"a" : {"$symbol" : "abababababab"}}
            'bson' => hex2bin('190000000E61000D0000006162616261626162616261620000'),
        ];

        yield 'Code With Scope' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::CODEWITHSCOPE,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 11,
                'dataLength' => 25,
            ]],
            // {"a" : {"$code" : "abcd", "$scope" : {"x" : {"$numberInt": "1"}}}}
            'bson' => hex2bin('210000000F6100190000000500000061626364000C000000107800010000000000'),
        ];

        yield 'Int32' => [
            'expected' => [[
                'key' => 'i',
                'bsonType' => Type::INT32,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 4,
            ]],
            // {"i" : {"$numberInt": "-2147483648"}}
            'bson' => hex2bin('0C0000001069000000008000'),
        ];

        yield 'Timestamp' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::TIMESTAMP,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 8,
            ]],
            // {"a" : {"$timestamp" : {"t" : 123456789, "i" : 42} } }
            'bson' => hex2bin('100000001161002A00000015CD5B0700'),
        ];

        yield 'Int64' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::INT64,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 8,
            ]],
            // {"a" : {"$numberLong" : "-9223372036854775808"}}
            'bson' => hex2bin('10000000126100000000000000008000'),
        ];

        yield 'Decimal128' => [
            'expected' => [[
                'key' => 'd',
                'bsonType' => Type::DECIMAL128,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => 7,
                'dataLength' => 16,
            ]],
            // {"d": { "$numberDecimal": "0.000001234567890123456789012345678901234" }}
            'bson' => hex2bin('18000000136400F2AF967ED05C82DE3297FF6FDE3CF22F00'),
        ];

        yield 'MinKey' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::MINKEY,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => null,
                'dataLength' => 0,
            ]],
            // {"a" : {"$minKey" : 1}}
            'bson' => hex2bin('08000000FF610000'),
        ];

        yield 'MaxKey' => [
            'expected' => [[
                'key' => 'a',
                'bsonType' => Type::MAXKEY,
                'keyOffset' => 5,
                'keyLength' => 1,
                'dataOffset' => null,
                'dataLength' => 0,
            ]],
            // {"a" : {"$maxKey" : 1}}
            'bson' => hex2bin('080000007F610000'),
        ];
    }
}
