<?php

namespace MongoDB\Tests\Aggregation\Converter\Stage;

use Generator;
use MongoDB\Aggregation\Converter\Stage\MatchStage as MatchConverter;
use MongoDB\Aggregation\Stage\MatchStage;
use PHPUnit\Framework\TestCase;

class MatchConverterTest extends TestCase
{
    /** @dataProvider provideMatchExpressions */
    public function testConvert($matchExpr)
    {
        $stage = new MatchStage($matchExpr);
        $converter = new MatchConverter();

        $this->assertEquals(
            (object) ['$match' => (object) ['foo' => 'bar']],
            $converter->convert($stage)
        );
    }

    public static function provideMatchExpressions(): Generator
    {
        yield 'array' => ['matchExpr' => ['foo' => 'bar']];
        yield 'object' => ['matchExpr' => (object) ['foo' => 'bar']];
    }

    public function testSupports()
    {
        $converter = new MatchConverter();

        $this->assertTrue($converter->supports(new MatchStage(['foo' => 'bar'])));
        $this->assertFalse($converter->supports('foo'));
    }
}
