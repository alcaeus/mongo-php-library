<?php

namespace MongoDB\Tests\Aggregation\Converter\Stage;

use Generator;
use MongoDB\Aggregation\Converter\Stage\SortStageConverter;
use MongoDB\Aggregation\Stage\SortStage;
use PHPUnit\Framework\TestCase;

class SortStageConverterTest extends TestCase
{
    /** @dataProvider provideSortExpressions */
    public function testConvert($sortExpr)
    {
        $stage = new SortStage($sortExpr);
        $converter = new SortStageConverter();

        $this->assertEquals(
            (object) ['$sort' => (object) ['foo' => 1]],
            $converter->encode($stage)
        );
    }

    public static function provideSortExpressions(): Generator
    {
        yield 'array' => ['sortExpr' => ['foo' => 1]];
        yield 'object' => ['sortExpr' => (object) ['foo' => 1]];
    }

    public function testSupports()
    {
        $converter = new SortStageConverter();

        $this->assertTrue($converter->canEncode(new SortStage(['foo' => 1])));
        $this->assertFalse($converter->canEncode('foo'));
    }
}
