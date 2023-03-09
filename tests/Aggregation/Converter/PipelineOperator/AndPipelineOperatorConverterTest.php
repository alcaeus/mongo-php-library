<?php

namespace MongoDB\Tests\Aggregation\Converter\PipelineOperator;

use MongoDB\Aggregation\Converter\PipelineOperator\AndPipelineOperatorConverter;
use MongoDB\Aggregation\Converter\PipelineOperator\EqPipelineOperatorConverter;
use MongoDB\Aggregation\PipelineOperator\AndPipelineOperator;
use MongoDB\Aggregation\PipelineOperator\EqPipelineOperator;
use MongoDB\Codec\CodecLibrary;
use PHPUnit\Framework\TestCase;

class AndPipelineOperatorConverterTest extends TestCase
{
    public function testConvert()
    {
        $operator = new AndPipelineOperator('foo', 'bar');
        $converter = new AndPipelineOperatorConverter();

        $this->assertEquals(
            (object) ['$and' => ['foo', 'bar']],
            $converter->encode($operator)
        );
    }

    public function testConvertTraversesConverting()
    {
        $operator = new AndPipelineOperator(
            new EqPipelineOperator('foo', 'bar'),
            new EqPipelineOperator('bar', 'baz')
        );

        $converter = new AndPipelineOperatorConverter();
        new CodecLibrary($converter, new EqPipelineOperatorConverter());

        $this->assertEquals(
            (object) ['$and' => [
                (object) ['$eq' => ['foo', 'bar']],
                (object) ['$eq' => ['bar', 'baz']],
            ]],
            $converter->encode($operator)
        );
    }

    public function testSupports()
    {
        $converter = new AndPipelineOperatorConverter();

        $this->assertTrue($converter->canEncode(new AndPipelineOperator([], [])));
        $this->assertFalse($converter->canEncode('foo'));
    }
}
