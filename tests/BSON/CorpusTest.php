<?php

namespace MongoDB\Tests\BSON;

use Generator;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\PHPBSON\Document;
use MongoDB\Tests\TestCase;
use function array_column;
use function array_combine;
use function array_filter;
use function array_intersect_key;
use function array_keys;
use function array_map;
use function array_merge;
use function file_get_contents;
use function glob;
use function hex2bin;
use function json_decode;

final class CorpusTest extends TestCase
{
    static array $tests = [];

    /** @dataProvider provideValidTests */
    public function testCanonicalBson(
        string $canonicalBson,
        string $canonicalExtJson,
        string $relaxed_extjson,
        string $degenerate_bson,
        string $degenerate_extjson,
        string $converted_bson,
        string $converted_extjson,
        bool $lossy,
    ): void {
        $document = Document::fromBSON(hex2bin($canonicalBson));
        self::assertSame(hex2bin($canonicalBson), (string) $document);
    }

    /**
     * @dataProvider provideDegenerateBsonTests
     * @doesNotPerformAssertions
     */
    public function testDegenerateBson(
        string $canonicalBson,
        string $canonical_extjson,
        string $relaxed_extjson,
        string $degenerate_bson,
        string $degenerate_extjson,
        string $converted_bson,
        string $converted_extjson,
        bool $lossy,
    ): void {
        Document::fromBSON(hex2bin($degenerate_bson));
    }

    public static function provideDegenerateBsonTests(): array
    {
        return array_filter(
            self::provideValidTests(),
            fn (array $test): bool => $test['degenerate_bson'] !== '',
        );
    }

    public static function provideValidTests(): array
    {
        $emptyTest = [
            'canonical_bson' => '',
            'canonical_extjson' => '',
            'relaxed_extjson' => '',
            'degenerate_bson' => '',
            'degenerate_extjson' => '',
            'converted_bson' => '',
            'converted_extjson' => '',
            'lossy' => false,
        ];

        return array_map(
            fn (array $test) => array_intersect_key(array_merge($emptyTest, $test), $emptyTest),
            self::provideTests(__DIR__ . '/bson-corpus/*.json', 'valid'),
        );
    }

    /** @dataProvider provideDecodeErrorTests */
    public function testDecodeErrors(string $bson): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Document::fromBSON($bson);
    }

    public static function provideDecodeErrorTests(): Generator
    {
        $emptyTest = ['bson' => ''];

        yield from array_map(
            fn (array $test) => array_intersect_key(array_merge($emptyTest, $test), $emptyTest),
            self::provideTests(__DIR__ . '/bson-corpus/*.json', 'decodeErrors'),
        );
    }

    // TODO: Parse errors (needs JSON parser)

    private static function provideTests(string $pattern, string $key): array
    {
        $tests = [];

        foreach (glob($pattern) as $filename) {
            $fileTests = self::readTestFile($filename);
            $group = $fileTests['description'] . ' (' . basename($filename) . ')';

            $groupTests = array_column($fileTests[$key] ?? [], null, 'description');
            $tests[] = array_combine(
                array_map(
                    fn (string $key) => $group . '/' . $key,
                    array_keys($groupTests),
                ),
                $groupTests,
            );
        }

        return array_merge(...$tests);
    }

    private static function readTestFile(string $filename): array
    {
        return static::$tests[$filename] ??= json_decode(file_get_contents($filename), true);
    }
}
