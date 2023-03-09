<?php

namespace MongoDB\Tests\Aggregation\Converter\PipelineOperator;

use Generator;
use MongoDB\Aggregation\Converter\PipelineOperator\EqPipelineOperatorConverter;
use MongoDB\Aggregation\Converter\PipelineOperator\FilterPipelineOperatorConverter;
use MongoDB\Aggregation\PipelineOperator\EqPipelineOperator;
use MongoDB\Aggregation\PipelineOperator\FilterPipelineOperator;
use MongoDB\Codec\CodecLibrary;
use PHPUnit\Framework\TestCase;

class FilterPipelineOperatorConverterTest extends TestCase
{
    /** @dataProvider provideOperatorParameters */
    public function testConvert($expected, $input, $cond, $as, $limit)
    {
        $operator = new FilterPipelineOperator($input, $cond, $as, $limit);
        $converter = new FilterPipelineOperatorConverter();

        $this->assertEquals(
            $expected,
            $converter->encode($operator)
        );
    }

    public static function provideOperatorParameters(): Generator
    {
        yield 'All fields' => [
            'expected' => (object) ['$filter' => (object) ['input' => 'some input', 'cond' => 'some condition', 'as' => 'fieldName', 'limit' => 13]],
            'input' => 'some input',
            'cond' => 'some condition',
            'as' => 'fieldName',
            'limit' => 13,
        ];

        yield 'Only required' => [
            'expected' => (object) ['$filter' => (object) ['input' => 'some input', 'cond' => 'some condition']],
            'input' => 'some input',
            'cond' => 'some condition',
            'as' => null,
            'limit' => null,
        ];
    }

    public function testConvertNested()
    {
        $operator = new FilterPipelineOperator('some input', new EqPipelineOperator(1, 2), null, null);

        $converter = new FilterPipelineOperatorConverter();
        new CodecLibrary($converter, new EqPipelineOperatorConverter());

        $this->assertEquals(
            (object) [
                '$filter' => (object) [
                    'input' => 'some input',
                    'cond' => (object) ['$eq' => [1, 2]],
                ],
            ],
            $converter->encode($operator)
        );
    }

    public function testSupports()
    {
        $converter = new FilterPipelineOperatorConverter();

        $this->assertTrue($converter->canEncode(new FilterPipelineOperator([], [], null, 1)));
        $this->assertFalse($converter->canEncode('foo'));
    }
}
