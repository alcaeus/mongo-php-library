<?php

namespace MongoDB\Tests\Aggregation\Converter\PipelineOperator;

use MongoDB\Aggregation\Converter\PipelineOperator\EqConverter;
use MongoDB\Aggregation\PipelineOperator\Eq;
use PHPUnit\Framework\TestCase;

class EqConverterTest extends TestCase
{
    public function testConvert()
    {
        $operator = new Eq('foo', 'bar');
        $converter = new EqConverter();

        $this->assertEquals(
            (object) ['$eq' => ['foo', 'bar']],
            $converter->convert($operator)
        );
    }

    public function testSupports()
    {
        $converter = new EqConverter();

        $this->assertTrue($converter->supports(new Eq([], [])));
        $this->assertFalse($converter->supports('foo'));
    }
}
