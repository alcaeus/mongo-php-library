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
use function basename;
use function bin2hex;
use function file_get_contents;
use function glob;
use function hex2bin;
use function in_array;
use function json_decode;

final class CorpusTest extends TestCase
{
    static array $tests = [];
    static array $skippedFiles = ['decimal128-1.json', 'decimal128-2.json', 'decimal128-3.json', 'decimal128-4.json', 'decimal128-5.json'];

    /** @dataProvider provideValidTests */
    public function testCanonicalBsonToCanonicalExtendedJson(
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

        self::assertSame(
            $this->canonicalizeJson($canonicalExtJson),
            $this->canonicalizeJson($document->toCanonicalExtendedJSON()),
        );
    }

    /** @dataProvider provideValidTestsWithRelaxedExtendedJson */
    public function testCanonicalBsonToRelaxedExtendedJson(
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

        self::assertSame(
            $this->canonicalizeJson($relaxed_extjson),
            $this->canonicalizeJson($document->toRelaxedExtendedJSON()),
        );
    }

    /**
     * @dataProvider provideDegenerateBsonTests
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
        $document = Document::fromBSON(hex2bin($degenerate_bson));

        // TODO: we have no intermediate representation, so degenerate BSON will
        // end up being degenerate after creating a Document instance
        self::assertSame(
            $canonicalBson,
            bin2hex((string) $document),
        );
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

    public function provideValidTestsWithRelaxedExtendedJson(): array
    {
        return array_filter(
            self::provideValidTests(),
            fn (array $test): bool => ($test['relaxed_extjson'] ?? '') !== ''
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
            $basename = basename($filename);
            if (in_array($basename, self::$skippedFiles)) {
                continue;
            }

            $fileTests = self::readTestFile($filename);
            $group = $fileTests['description'] . ' (' . $basename . ')';

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

    private function canonicalizeJson(string $json): string
    {
        $json = json_encode(json_decode($json, flags: JSON_THROW_ON_ERROR));

        /* Canonicalize string values for $numberDouble to ensure they are converted
         * the same as number literals in legacy and relaxed output. This is needed
         * because the printf format in _bson_as_json_visit_double uses a high level
         * of precision and may not produce the exponent notation expected by the
         * BSON corpus tests. */
        $json = preg_replace_callback(
            '/{"\$numberDouble":"(-?\d+(\.\d+([eE]\+\d+)?)?)"}/',
            function ($matches) {
                return '{"$numberDouble":"' . json_encode(json_decode($matches[1])) . '"}';
            },
            $json
        );

        return $json;
    }

}
