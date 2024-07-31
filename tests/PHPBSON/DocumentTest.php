<?php

namespace MongoDB\Tests\PHPBSON;

use Generator;
use InvalidArgumentException;
use MongoDB\PHPBSON\Document;
use MongoDB\Tests\TestCase;

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
}
