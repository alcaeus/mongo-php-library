<?php

namespace MongoDB\Benchmark\BSON;

use Exception;
use Generator;
use MongoDB\Benchmark\Fixtures\Data;
use MongoDB\BSON\Document;
use MongoDB\PHPBSON\Document as PHPDocument;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use stdClass;

use function file_get_contents;
use function iterator_to_array;

#[BeforeMethods('prepareData')]
#[Revs(10)]
#[Warmup(1)]
final class DocumentBench
{
    private static Document $document;
    private static PHPDocument $phpDocument;

    public function prepareData(): void
    {
        self::$document = Document::fromJSON(file_get_contents(Data::LARGE_FILE_PATH));
        self::$phpDocument = PHPDocument::fromBSON((string) self::$document);
    }

    #[ParamProviders('provideParams')]
    public static function provideParams(): Generator
    {
        yield 'Extension' => ['key' => 'bson'];
        yield 'Library' => ['key' => 'php'];
    }

    #[ParamProviders('provideParams')]
    public function benchCheckFirst(array $params): void
    {
        self::getDocument($params['key'])->has('qx3MigjubFSm');
    }

    #[ParamProviders('provideParams')]
    public function benchCheckLast(array $params): void
    {
        self::getDocument($params['key'])->has('Zz2MOlCxDhLl');
    }

    #[ParamProviders('provideParams')]
    public function benchAccessFirst(array $params): void
    {
        self::getDocument($params['key'])->get('qx3MigjubFSm');
    }

    #[ParamProviders('provideParams')]
    public function benchAccessLast(array $params): void
    {
        self::getDocument($params['key'])->get('Zz2MOlCxDhLl');
    }

    #[ParamProviders('provideParams')]
    public function benchIteratorToArray(array $params): void
    {
        iterator_to_array(self::getDocument($params['key']));
    }

    #[ParamProviders('provideParams')]
    public function benchToPHPObject(array $params): void
    {
        self::getDocument($params['key'])->toPHP();
    }

    #[ParamProviders('provideParams')]
    public function benchToPHPObjectViaIteration(array $params): void
    {
        $object = new stdClass();

        foreach (self::getDocument($params['key']) as $key => $value) {
            $object->$key = $value;
        }
    }

    #[ParamProviders('provideParams')]
    public function benchToPHPArray(array $params): void
    {
        self::getDocument($params['key'])->toPHP(['root' => 'array']);
    }

    #[ParamProviders('provideParams')]
    public function benchIteration(array $params): void
    {
        // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedForeach
        // phpcs:ignore Generic.ControlStructures.InlineControlStructure.NotAllowed
        foreach (self::getDocument($params['key']) as $key => $value);
    }

    #[ParamProviders('provideParams')]
    public function benchIterationAsArray(array $params): void
    {
        // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedForeach
        // phpcs:ignore Generic.ControlStructures.InlineControlStructure.NotAllowed
        foreach (self::getDocument($params['key'])->toPHP(['root' => 'array']) as $key => $value);
    }

    #[ParamProviders('provideParams')]
    public function benchIterationAsObject(array $params): void
    {
        // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedForeach
        // phpcs:ignore Generic.ControlStructures.InlineControlStructure.NotAllowed
        foreach (self::getDocument($params['key'])->toPHP() as $key => $value);
    }

    private static function getDocument(string $key): mixed
    {
        return match ($key) {
            'bson' => self::$document,
            'php' => self::$phpDocument,
            default => throw new Exception('Invalid key ' . $key),
        };
    }
}
