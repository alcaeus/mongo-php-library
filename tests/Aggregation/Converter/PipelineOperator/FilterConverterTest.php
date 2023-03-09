<?php

namespace MongoDB\Tests\Aggregation\Converter\PipelineOperator;

use Generator;
use MongoDB\Aggregation\Converter\PipelineOperator\Eq as EqConverter;
use MongoDB\Aggregation\Converter\PipelineOperator\Filter as FilterConverter;
use MongoDB\Aggregation\PipelineOperator\Eq;
use MongoDB\Aggregation\PipelineOperator\Filter;
use MongoDB\Codec\CodecLibrary;
use PHPUnit\Framework\TestCase;

class FilterConverterTest extends TestCase
{
    /** @dataProvider provideOperatorParameters */
    public function testConvert($expected, $input, $cond, $as, $limit)
    {
        $operator = new Filter($input, $cond, $as, $limit);
        $converter = new FilterConverter();

        $this->assertEquals(
            $expected,
            $converter->convert($operator)
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
        $operator = new Filter('some input', new Eq(1, 2), null, null);

        $converter = new FilterConverter();
        new CodecLibrary($converter, new EqConverter());

        $this->assertEquals(
            (object) [
                '$filter' => (object) [
                    'input' => 'some input',
                    'cond' => (object) ['$eq' => [1, 2]],
                ],
            ],
            $converter->convert($operator)
        );
    }

    public function testSupports()
    {
        $converter = new FilterConverter();

        $this->assertTrue($converter->supports(new Filter([], [], null, 1)));
        $this->assertFalse($converter->supports('foo'));
    }
}
