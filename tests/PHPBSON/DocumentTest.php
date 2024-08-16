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
use MongoDB\Tests\TestCase;

use function base64_decode;
use function hex2bin;
use function pack;

class DocumentTest extends TestCase
{
    /** @dataProvider provideInvalidBSONData */
    public function testFromBSONWithInvalidData(string $data, string $expectedException): void
    {
        $this->expectExceptionObject(new InvalidArgumentException($expectedException));

        Document::fromBSON($data);
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
        $encodedBSON = '0D000000037800050000000000';
        $document = Document::fromBSON(hex2bin($encodedBSON));

        self::assertSame(hex2bin($encodedBSON), (string) $document);
    }

    public function testFromJSON(): void
    {
        $document = Document::fromJSON('{"a" : "abababababab"}');
        self::assertSame(hex2bin('190000000261000D0000006162616261626162616261620000'), (string) $document);
    }

    public function testFromPHP(): void
    {
        $document = Document::fromPHP(['a' => 'abababababab']);
        self::assertSame(hex2bin('190000000261000D0000006162616261626162616261620000'), (string) $document);
    }

    public function testHas(): void
    {
        $document = Document::fromJSON('{"a" : "abababababab"}');
        self::assertTrue($document->has('a'));
        self::assertTrue(isset($document['a']));

        self::assertFalse($document->has('b'));
        self::assertFalse(isset($document['b']));
    }

    public function testGet(): void
    {
        $document = Document::fromJSON('{"a" : "abababababab"}');
        self::assertSame('abababababab', $document->get('a'));
        self::assertSame('abababababab', $document['a']);
    }

    /** @dataProvider getValidValues */
    public function testGetValues(mixed $expected, string $bson): void
    {
        $document = Document::fromBSON($bson);

        self::assertTrue($document->has('a'));
        self::assertEquals($expected, $document->get('a'));
    }

    public static function getValidValues(): Generator
    {
        yield 'Double' => [
            'expected' => 1.0,
            // {"a" : {"$numberDouble": "1.0"}}
            'bson' => hex2bin('10000000016100000000000000F03F00'),
        ];

        yield 'String' => [
            'expected' => 'abababababab',
            // {"a" : "abababababab"}
            'bson' => hex2bin('190000000261000D0000006162616261626162616261620000'),
        ];

        yield 'Document' => [
            'expected' => Document::fromPHP(['a' => 'b']),
            // {"a" : {"a" : "b"}}
            'bson' => hex2bin('160000000361000E0000000261000200000062000000'),
        ];

        yield 'Array' => [
            // TODO: Use PackedArray class
            'expected' => Document::fromPHP([10]),
            // {"a" : [{"$numberInt": "10"}]}
            'bson' => hex2bin('140000000461000C0000001030000A0000000000'),
        ];

        yield 'Binary (Sub-type 0)' => [
            'expected' => new Binary(base64_decode('//8='), Binary::TYPE_GENERIC),
            // {"a" : { "$binary" : {"base64" : "//8=", "subType" : "00"}}}
            'bson' => hex2bin('0F0000000561000200000000FFFF00'),
        ];

        yield 'Undefined' => [
            'expected' => Undefined::__set_state([]),
            // {"a" : {"$undefined" : true}}
            'bson' => hex2bin('0800000006610000'),
        ];

        yield 'ObjectId' => [
            'expected' => new ObjectId('000000000000000000000000'),
            // {"a" : {"$oid" : "000000000000000000000000"}}
            'bson' => hex2bin('1400000007610000000000000000000000000000'),
        ];

        yield 'Boolean (true)' => [
            'expected' => true,
            // {"a" : true}
            'bson' => hex2bin('090000000861000100'),
        ];

        yield 'Boolean (false)' => [
            'expected' => false,
            // {"a" : false}
            'bson' => hex2bin('090000000861000000'),
        ];

        yield 'UTCDateTime' => [
            'expected' => new UTCDateTime(0),
            // {"a" : {"$date" : {"$numberLong" : "0"}}}
            'bson' => hex2bin('10000000096100000000000000000000'),
        ];

        yield 'Null' => [
            'expected' => null,
            // {"a" : null}
            'bson' => hex2bin('080000000A610000'),
        ];

        yield 'Regex' => [
            'expected' => new Regex('abc', 'im'),
            // {"a" : {"$regularExpression" : { "pattern": "abc", "options" : "im"}}}
            'bson' => hex2bin('0F0000000B610061626300696D0000'),
        ];

        yield 'DBPointer' => [
            'expected' => DBPointer::__set_state([
                'ref' => 'b',
                'id' => '56e1fc72e0c917e9c4714161',
            ]),
            // {"a": {"$dbPointer": {"$ref": "b", "$id": {"$oid": "56e1fc72e0c917e9c4714161"}}}}
            'bson' => hex2bin('1A0000000C610002000000620056E1FC72E0C917E9C471416100'),
        ];

        yield 'Code' => [
            'expected' => new Javascript('abababababab'),
            // {"a" : {"$code" : "abababababab"}}
            'bson' => hex2bin('190000000D61000D0000006162616261626162616261620000'),
        ];

        yield 'Symbol' => [
            'expected' => Symbol::__set_state(['symbol' => 'abababababab']),
            // {"a" : {"$symbol" : "abababababab"}}
            'bson' => hex2bin('190000000E61000D0000006162616261626162616261620000'),
        ];

        yield 'Code With Scope' => [
            'expected' => new Javascript('abcd', Document::fromPHP(['x' => 1])),
            // {"a" : {"$code" : "abcd", "$scope" : {"x" : {"$numberInt": "1"}}}}
            'bson' => hex2bin('210000000F6100190000000500000061626364000C000000107800010000000000'),
        ];

        yield 'Int32' => [
            'expected' => -2147483648,
            // {"a" : {"$numberInt": "-2147483648"}}
            'bson' => hex2bin('0C0000001061000000008000'),
        ];

        yield 'Timestamp' => [
            'expected' => new Timestamp(42, 123456789),
            // {"a" : {"$timestamp" : {"t" : 123456789, "i" : 42} } }
            'bson' => hex2bin('100000001161002A00000015CD5B0700'),
        ];

        yield 'Int64' => [
            'expected' => new Int64('-9223372036854775808'),
            // {"a" : {"$numberLong" : "-9223372036854775808"}}
            'bson' => hex2bin('10000000126100000000000000008000'),
        ];

        // TODO: Not implemented
//        yield 'Decimal128' => [
//            'expected' => new Decimal128('0.000001234567890123456789012345678901234'),
//            // {"a": { "$numberDecimal": "0.000001234567890123456789012345678901234" }}
//            'bson' => hex2bin('18000000136100F2AF967ED05C82DE3297FF6FDE3CF22F00'),
//        ];

        yield 'MinKey' => [
            'expected' => new MinKey(),
            // {"a" : {"$minKey" : 1}}
            'bson' => hex2bin('08000000FF610000'),
        ];

        yield 'MaxKey' => [
            'expected' => new MaxKey(),
            // {"a" : {"$maxKey" : 1}}
            'bson' => hex2bin('080000007F610000'),
        ];
    }
}
