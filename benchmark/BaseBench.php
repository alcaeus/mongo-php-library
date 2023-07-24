<?php

namespace MongoDB\Benchmark;

use MongoDB\Client;
use MongoDB\Collection;

use function getenv;

abstract class BaseBench
{
    private static ?Collection $collection;

    protected static function getCollection(): Collection
    {
        return self::$collection ??= self::createCollection();
    }

    public static function createCollection(): Collection
    {
        $client = new Client(self::getUri());

        return $client->selectCollection(self::getDatabase(), 'perftest');
    }

    private static function getUri(): string
    {
        return getenv('MONGODB_URI') ?: 'mongodb://localhost:27017/';
    }

    private static function getDatabase(): string
    {
        return getenv('MONGODB_DATABASE') ?: 'phplib_test';
    }
}
