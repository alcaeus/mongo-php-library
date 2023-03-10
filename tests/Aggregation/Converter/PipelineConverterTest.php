<?php

namespace MongoDB\Tests\Aggregation\Converter;

use Generator;
use MongoDB\Aggregation\Converter\PipelineConverter;
use MongoDB\Aggregation\Factory\PipelineOperatorFactory;
use MongoDB\Aggregation\Factory\QueryOperatorFactory;
use MongoDB\Aggregation\Factory\StageFactory;
use MongoDB\Aggregation\Pipeline;
use MongoDB\Aggregation\PipelineOperator\EqPipelineOperator;
use MongoDB\Aggregation\QueryOperator\ExprQueryOperator;
use MongoDB\Aggregation\Stage\LimitStage;
use MongoDB\Aggregation\Stage\MatchStage;
use MongoDB\Aggregation\Stage\SortStage;
use PHPUnit\Framework\TestCase;
use function MongoDB\Aggregation\FactoryFunctions\PipelineOperator\eq;
use function MongoDB\Aggregation\FactoryFunctions\QueryOperator\expr;
use function MongoDB\Aggregation\FactoryFunctions\Stage\limit;
use function MongoDB\Aggregation\FactoryFunctions\Stage\matchStage as matchStageFn;
use function MongoDB\Aggregation\FactoryFunctions\Stage\sort;

class PipelineConverterTest extends TestCase
{
    /** @dataProvider providePipelines */
    public function testConvert($pipeline)
    {
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

    public static function providePipelines(): Generator
    {
        $pipeline = new Pipeline(
            new MatchStage(['foo' => 'bar']),
            new MatchStage(new ExprQueryOperator(new EqPipelineOperator('foo', 'bar'))),
            new Pipeline(new SortStage(['name' => 1]), new LimitStage(5))
        );
        yield 'Objects' => ['pipeline' => $pipeline];

        $pipeline = new Pipeline(
            StageFactory::match(['foo' => 'bar']),
            StageFactory::match(
                QueryOperatorFactory::expr(
                    PipelineOperatorFactory::eq('foo', 'bar')
                )
            ),
            StageFactory::sort(['name' => 1]),
            StageFactory::limit(5)
        );
        yield 'From Factory' => ['pipeline' => $pipeline];

        $pipeline = new Pipeline(
            matchStageFn(['foo' => 'bar']),
            matchStageFn(
                expr(eq('foo', 'bar'))
            ),
            sort(['name' => 1]),
            limit(5)
        );
        yield 'From Factory Functions' => ['pipeline' => $pipeline];
    }

    public function testSupports()
    {
        $converter = new PipelineConverter();

        $this->assertTrue($converter->canEncode(new Pipeline()));
        $this->assertFalse($converter->canEncode('foo'));
    }
}
