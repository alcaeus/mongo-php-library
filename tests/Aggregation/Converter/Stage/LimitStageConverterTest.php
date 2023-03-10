<?php

namespace MongoDB\Tests\Aggregation\Converter\Stage;

use Generator;
use MongoDB\Aggregation\Converter\Stage\LimitStageConverter;
use MongoDB\Aggregation\Stage\LimitStage;
use PHPUnit\Framework\TestCase;

class LimitStageConverterTest extends TestCase
{
    public function testConvert()
    {
        $stage = new LimitStage(1);
        $converter = new LimitStageConverter();

        $this->assertEquals(
            (object) ['$limit' => 1],
            $converter->encode($stage)
        );
    }

    public function testSupports()
    {
        $converter = new LimitStageConverter();

        $this->assertTrue($converter->canEncode(new LimitStage(1)));
        $this->assertFalse($converter->canEncode('foo'));
    }
}
