<?php

namespace MongoDB\Benchmark\DriverBench;

use Generator;
use MongoDB\Benchmark\Fixtures\Data;
use MongoDB\Benchmark\Utils;
use MongoDB\BSON\Document;
use MongoDB\Driver\Command;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\ParamProviders;

use function array_fill;
use function array_map;
use function file_get_contents;
use function range;

/**
 * For accurate results, run benchmarks on a standalone server.
 */
#[BeforeMethods('prepareDatabase')]
final class InsertVsMergeBench
{
    public function prepareDatabase(): void
    {
        Utils::getCollection()->drop();
        Utils::getDatabase()->createCollection(Utils::getCollectionName());
    }

    /** @param array{document: object|array, repeat: int, options?: array} $params */
    #[ParamProviders('provideDocuments')]
    public function benchInsertOne(array $params): void
    {
        $collection = Utils::getCollection();

        for ($i = $params['repeat']; $i > 0; $i--) {
            $collection->insertOne($params['document']);
        }
    }

    /** @param array{document: object|array, repeat: int, options?: array} $params */
    #[ParamProviders('provideDocuments')]
    public function benchInsertMany(array $params): void
    {
        $collection = Utils::getCollection();

        $collection->insertMany($this->getDocumentsWithIdentifiers($params['document'], $params['repeat']));
    }

    /** @param array{document: object|array, repeat: int, options?: array} $params */
    #[ParamProviders('provideDocuments')]
    public function benchMergeWithReplace(array $params): void
    {
        $database = Utils::getDatabase();

        $database->aggregate(
            [
                ['$documents' => $this->getDocumentsWithIdentifiers($params['document'], $params['repeat'])],
                ['$merge' => ['into' => Utils::getCollectionName(), 'whenMatched' => 'replace', 'whenNotMatched' => 'insert']],
            ],
        );
    }

    /** @param array{document: object|array, repeat: int, options?: array} $params */
    #[ParamProviders('provideDocuments')]
    public function benchMergeWithMerge(array $params): void
    {
        $database = Utils::getDatabase();

        $database->aggregate(
            [
                ['$documents' => $this->getDocumentsWithIdentifiers($params['document'], $params['repeat'])],
                ['$merge' => ['into' => Utils::getCollectionName(), 'whenMatched' => 'merge', 'whenNotMatched' => 'insert']],
            ],
        );
    }

    /** @param array{document: object|array, repeat: int, options?: array} $params */
    #[ParamProviders('provideDocuments')]
    public function benchOut(array $params): void
    {
        $database = Utils::getDatabase();

        $database->aggregate(
            [
                ['$documents' => $this->getDocumentsWithIdentifiers($params['document'], $params['repeat'])],
                ['$out' => Utils::getCollectionName()],
            ],
        );
    }

    public static function provideDocuments(): Generator
    {
        yield 'Small doc' => [
            'document' => Data::readJsonFile(Data::SMALL_FILE_PATH),
            'repeat' => 1_000,
        ];

        yield 'Large doc' => [
            'document' => Data::readJsonFile(Data::LARGE_FILE_PATH),
            'repeat' => 1,
        ];
    }

    private function getDocumentsWithIdentifiers(array $document, int $repeat): array
    {
        $result = [];
        for ($i = 0; $i < $repeat; $i++) {
            $result[] = ['_id' => $i] + $document;
        }

        return $result;
    }
}
