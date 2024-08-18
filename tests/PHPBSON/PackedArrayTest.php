<?php

namespace MongoDB\Tests\PHPBSON;

use Generator;
use InvalidArgumentException;
use MongoDB\BSON\Binary;
use MongoDB\BSON\DBPointer;
use MongoDB\BSON\Int64;
use MongoDB\BSON\Javascript;
use MongoDB\BSON\MaxKey;
use MongoDB\BSON\MinKey;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\Regex;
use MongoDB\BSON\Symbol;
use MongoDB\BSON\Timestamp;
use MongoDB\BSON\Undefined;
use MongoDB\BSON\UTCDateTime;
use MongoDB\PHPBSON\Document;
use MongoDB\PHPBSON\PackedArray;
use MongoDB\Tests\TestCase;

use function base64_decode;
use function hex2bin;
use function pack;

class PackedArrayTest extends TestCase
{
    /** @dataProvider provideInvalidBSONData */
    public function testFromBSONWithInvalidData(string $data, string $expectedException): void
    {
        $this->expectExceptionObject(new InvalidArgumentException($expectedException));

        PackedArray::fromBSON($data);
    }

    public static function provideInvalidBSONData(): Generator
    {
        yield 'Empty string' => [
            'data' => '',
            'expectedException' => 'Invalid BSON data',
        ];

        yield 'Not enough data' => [
            'data' => pack('Lx', 2),
            'expectedException' => 'Invalid BSON length',
        ];

        yield 'Length does not match' => [
            'data' => pack('Lx', 10),
            'expectedException' => 'Invalid BSON length',
        ];

        yield 'Last byte not a null byte' => [
            'data' => pack('L', 10) . 'abcdef',
            'expectedException' => 'Invalid BSON length',
        ];
    }

    public function testFromBSONValid(): void
    {
        $encodedBSON = '0D000000043000050000000000';
        $packedArray = PackedArray::fromBSON(hex2bin($encodedBSON));

        self::assertSame(hex2bin($encodedBSON), (string) $packedArray);
    }

    public function testFromJSON(): void
    {
        $packedArray = PackedArray::fromJSON('["abababababab"]');
        self::assertSame(hex2bin('190000000230000d0000006162616261626162616261620000'), (string) $packedArray);
    }

    public function testFromPHP(): void
    {
        $packedArray = PackedArray::fromPHP(['abababababab']);
        self::assertSame(hex2bin('190000000230000d0000006162616261626162616261620000'), (string) $packedArray);
    }

    public function testHas(): void
    {
        $document = Document::fromJSON('["abababababab"]');
        self::assertTrue($document->has(0));
        self::assertTrue(isset($document[0]));

        self::assertFalse($document->has(1));
        self::assertFalse(isset($document[1]));
    }

    public function testGet(): void
    {
        $document = Document::fromJSON('["abababababab"]');
        self::assertSame('abababababab', $document->get(0));
        self::assertSame('abababababab', $document[0]);
    }

    /** @dataProvider getValidValues */
    public function testGetValues(mixed $expected, string $bson): void
    {
        $document = Document::fromBSON($bson);

        self::assertTrue($document->has(0));
        self::assertEquals($expected, $document->get(0));
    }

    public static function getValidValues(): Generator
    {
        yield 'Double' => [
            'expected' => 1.0,
            // [{"$numberDouble": "1.0"}]
            'bson' => hex2bin('10000000013000000000000000F03F00'),
        ];

        yield 'String' => [
            'expected' => 'abababababab',
            // ["abababababab"]
            'bson' => hex2bin('190000000230000D0000006162616261626162616261620000'),
        ];

        yield 'Document' => [
            'expected' => Document::fromPHP(['a' => 'b']),
            // [{"a" : "b"}]
            'bson' => hex2bin('160000000330000E0000000261000200000062000000'),
        ];

        yield 'Array' => [
            'expected' => PackedArray::fromPHP([10]),
            // [[{"$numberInt": "10"}]]
            'bson' => hex2bin('140000000430000C0000001030000A0000000000'),
        ];

        yield 'Binary (Sub-type 0)' => [
            'expected' => new Binary(base64_decode('//8='), Binary::TYPE_GENERIC),
            // [{ "$binary" : {"base64" : "//8=", "subType" : "00"}}]
            'bson' => hex2bin('0F0000000530000200000000FFFF00'),
        ];

        yield 'Undefined' => [
            'expected' => Undefined::__set_state([]),
            // [{"$undefined" : true}]
            'bson' => hex2bin('0800000006300000'),
        ];

        yield 'ObjectId' => [
            'expected' => new ObjectId('000000000000000000000000'),
            // [{"$oid" : "000000000000000000000000"}]
            'bson' => hex2bin('1400000007300000000000000000000000000000'),
        ];

        yield 'Boolean (true)' => [
            'expected' => true,
            // [true]
            'bson' => hex2bin('090000000830000100'),
        ];

        yield 'Boolean (false)' => [
            'expected' => false,
            // [false]
            'bson' => hex2bin('090000000830000000'),
        ];

        yield 'UTCDateTime' => [
            'expected' => new UTCDateTime(0),
            // [{"$date" : {"$numberLong" : "0"}}]
            'bson' => hex2bin('10000000093000000000000000000000'),
        ];

        yield 'Null' => [
            'expected' => null,
            // [null]
            'bson' => hex2bin('080000000A300000'),
        ];

        yield 'Regex' => [
            'expected' => new Regex('abc', 'im'),
            // [{"$regularExpression" : { "pattern": "abc", "options" : "im"}}]
            'bson' => hex2bin('0F0000000B300061626300696D0000'),
        ];

        yield 'DBPointer' => [
            'expected' => DBPointer::__set_state([
                'ref' => 'b',
                'id' => '56e1fc72e0c917e9c4714161',
            ]),
            // {"a": {"$dbPointer": {"$ref": "b", "$id": {"$oid": "56e1fc72e0c917e9c4714161"}}}}
            'bson' => hex2bin('1A0000000C300002000000620056E1FC72E0C917E9C471416100'),
        ];

        yield 'Code' => [
            'expected' => new Javascript('abababababab'),
            // [{"$code" : "abababababab"}]
            'bson' => hex2bin('190000000D30000D0000006162616261626162616261620000'),
        ];

        yield 'Symbol' => [
            'expected' => Symbol::__set_state(['symbol' => 'abababababab']),
            // [{"$symbol" : "abababababab"}]
            'bson' => hex2bin('190000000E30000D0000006162616261626162616261620000'),
        ];

        yield 'Code With Scope' => [
            'expected' => new Javascript('abcd', Document::fromPHP(['x' => 1])),
            // [{"$code" : "abcd", "$scope" : {"x" : {"$numberInt": "1"}}}]
            'bson' => hex2bin('210000000F3000190000000500000061626364000C000000107800010000000000'),
        ];

        yield 'Int32' => [
            'expected' => -2147483648,
            // [{"$numberInt": "-2147483648"}]
            'bson' => hex2bin('0C0000001030000000008000'),
        ];

        yield 'Timestamp' => [
            'expected' => new Timestamp(42, 123456789),
            // [{"$timestamp" : {"t" : 123456789, "i" : 42} } ]
            'bson' => hex2bin('100000001130002A00000015CD5B0700'),
        ];

        yield 'Int64' => [
            'expected' => new Int64('-9223372036854775808'),
            // [{"$numberLong" : "-9223372036854775808"}]
            'bson' => hex2bin('10000000123000000000000000008000'),
        ];

        // TODO: Not implemented
//        yield 'Decimal128' => [
//            'expected' => new Decimal128('0.000001234567890123456789012345678901234'),
//            // [{ "$numberDecimal": "0.000001234567890123456789012345678901234" }]
//            'bson' => hex2bin('18000000133000F2AF967ED05C82DE3297FF6FDE3CF22F00'),
//        ];

        yield 'MinKey' => [
            'expected' => new MinKey(),
            // [{"$minKey" : 1}]
            'bson' => hex2bin('08000000FF300000'),
        ];

        yield 'MaxKey' => [
            'expected' => new MaxKey(),
            // [{"$maxKey" : 1}]
            'bson' => hex2bin('080000007F300000'),
        ];
    }
}
