<?php

namespace MongoDB\Tests\Aggregation\Converter\PipelineOperator;

use MongoDB\Aggregation\Converter\PipelineOperator\NePipelineOperatorConverter;
use MongoDB\Aggregation\PipelineOperator\NePipelineOperator;
use PHPUnit\Framework\TestCase;

class NePipelineOperatorConverterTest extends TestCase
{
    public function testConvert()
    {
        $operator = new NePipelineOperator('foo', 'bar');
        $converter = new NePipelineOperatorConverter();

        $this->assertEquals(
            (object) ['$ne' => ['foo', 'bar']],
            $converter->encode($operator)
        );
    }

    public function testSupports()
    {
        $converter = new NePipelineOperatorConverter();

        $this->assertTrue($converter->canEncode(new NePipelineOperator([], [])));
        $this->assertFalse($converter->canEncode('foo'));
    }
}
