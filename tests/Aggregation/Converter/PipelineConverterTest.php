<?php

namespace MongoDB\Tests\Aggregation\Converter;

use Generator;
use MongoDB\Aggregation\Converter\PipelineConverter;
use MongoDB\Aggregation\Converter\Stage\MatchStageConverter;
use MongoDB\Aggregation\Pipeline;
use MongoDB\Aggregation\PipelineOperator\EqPipelineOperator;
use MongoDB\Aggregation\QueryOperator\ExprQueryOperator;
use MongoDB\Aggregation\Stage\LimitStage;
use MongoDB\Aggregation\Stage\MatchStage;
use MongoDB\Aggregation\Stage\SortStage;
use PHPUnit\Framework\TestCase;

class PipelineConverterTest extends TestCase
{
    public function testConvert()
    {
        $pipeline = new Pipeline(
            new MatchStage(['foo' => 'bar']),
            new MatchStage(new ExprQueryOperator(new EqPipelineOperator('foo', 'bar'))),
            new Pipeline(new SortStage(['name' => 1]), new LimitStage(5))
        );

        $converter = new PipelineConverter();

        $this->assertEquals(
            [
                (object) ['$match' => (object) ['foo' => 'bar']],
                (object) ['$match' => (object) ['$expr' => (object) ['$eq' => ['foo', 'bar']]]],
                (object) ['$sort' => (object) ['name' => 1]],
                (object) ['$limit' => 5],
            ],
            $converter->encode($pipeline)
        );
    }

    public function testSupports()
    {
        $converter = new PipelineConverter();

        $this->assertTrue($converter->canEncode(new Pipeline()));
        $this->assertFalse($converter->canEncode('foo'));
    }
}
