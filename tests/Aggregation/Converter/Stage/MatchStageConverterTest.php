<?php

namespace MongoDB\Tests\Aggregation\Converter\Stage;

use Generator;
use MongoDB\Aggregation\Converter\Stage\MatchStageConverter;
use MongoDB\Aggregation\Stage\MatchStage;
use PHPUnit\Framework\TestCase;
use function MongoDB\Aggregation\FactoryFunctions\PipelineOperator\gt;
use function MongoDB\Aggregation\FactoryFunctions\PipelineOperator\lt;

class MatchStageConverterTest extends TestCase
{
    /** @dataProvider provideMatchExpressions */
    public function testConvert($expected, $matchExpr)
    {
        $stage = new MatchStage($matchExpr);
        $converter = new MatchStageConverter();

        $this->assertEquals(
            (object) ['$match' => $expected],
            $converter->encode($stage)
        );
    }

    public static function provideMatchExpressions(): Generator
    {
        yield 'array' => [
            'expected' => (object) ['foo' => 'bar'],
            'matchExpr' => ['foo' => 'bar'],
        ];

        yield 'object' => [
            'expected' => (object) ['foo' => 'bar'],
            'matchExpr' => (object) ['foo' => 'bar'],
        ];

        yield 'expressions' => [
            'expected' => (object) [
                'foo' => ['$gt' => 1, '$lt' => 10],
            ],
            'matchExpr' => [
                gt('foo', 1),
                lt('foo', 10),
            ]
        ];
    }

    public function testSupports()
    {
        $converter = new MatchStageConverter();

        $this->assertTrue($converter->canEncode(new MatchStage(['foo' => 'bar'])));
        $this->assertFalse($converter->canEncode('foo'));
    }
}
