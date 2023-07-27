<?php

namespace MongoDB\Tests\Options;

use MongoDB\Driver\ReadConcern;
use MongoDB\Options\AggregateOptions;
use PHPUnit\Framework\TestCase;

class AggregateOptionsTest extends TestCase
{
    public function testFromArray(): void
    {
        $readConcern = new ReadConcern(ReadConcern::LOCAL);

        $instance = AggregateOptions::fromArray([
            'readConcern' => $readConcern,
            'typeMap' => ['root' => 'array'],
        ]);

        $this->assertSame($readConcern, $instance->getReadConcern());
        $this->assertSame(['root' => 'array'], $instance->getTypeMap());
    }

    public function testWithReadConcern(): void
    {
        $localReadConcern = new ReadConcern(ReadConcern::LOCAL);
        $majorityReadConcern = new ReadConcern(ReadConcern::MAJORITY);

        $instance = AggregateOptions::fromArray(['readConcern' => $localReadConcern]);
        $this->assertSame($localReadConcern, $instance->getReadConcern());

        // Overwrite option, assert we receive a different instance
        $withMajority = $instance->withReadConcern($majorityReadConcern);
        $this->assertNotSame($instance, $withMajority);
        $this->assertSame($majorityReadConcern, $withMajority->getReadConcern());
        $this->assertSame($localReadConcern, $instance->getReadConcern());

        // Change option without overwriting, assert we receive the same instance
        $notOverwritten = $withMajority->withReadConcern($localReadConcern, false);
        $this->assertSame($withMajority, $notOverwritten);
        $this->assertSame($majorityReadConcern, $notOverwritten->getReadConcern());

        // Remove readConcern option
        $withoutReadConcern = $instance->withReadConcern(null);
        $this->assertNotSame($instance, $withoutReadConcern);
        $this->assertNull($withoutReadConcern->getReadConcern());

        // Change option without overwriting, expect it to be changed because it's null
        $overwritten = $withoutReadConcern->withReadConcern($majorityReadConcern, false);
        $this->assertNotSame($withoutReadConcern, $overwritten);
        $this->assertSame($majorityReadConcern, $overwritten->getReadConcern());
    }
}
