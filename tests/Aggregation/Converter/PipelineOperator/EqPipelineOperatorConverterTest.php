<?php

namespace MongoDB\Tests\Aggregation\Converter\PipelineOperator;

use MongoDB\Aggregation\Converter\PipelineOperator\EqPipelineOperatorConverter;
use MongoDB\Aggregation\PipelineOperator\EqPipelineOperator;
use PHPUnit\Framework\TestCase;

class EqPipelineOperatorConverterTest extends TestCase
{
    public function testConvert()
    {
        $operator = new EqPipelineOperator('foo', 'bar');
        $converter = new EqPipelineOperatorConverter();

        $this->assertEquals(
            (object) ['$eq' => ['foo', 'bar']],
            $converter->encode($operator)
        );
    }

    public function testSupports()
    {
        $converter = new EqPipelineOperatorConverter();

        $this->assertTrue($converter->canEncode(new EqPipelineOperator([], [])));
        $this->assertFalse($converter->canEncode('foo'));
    }
}
