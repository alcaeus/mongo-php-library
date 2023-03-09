<?php

namespace MongoDB\Tests\Aggregation\Converter\PipelineOperator;

use MongoDB\Aggregation\Converter\PipelineOperator\NeConverter;
use MongoDB\Aggregation\PipelineOperator\Ne;
use PHPUnit\Framework\TestCase;

class NeConverterTest extends TestCase
{
    public function testConvert()
    {
        $operator = new Ne('foo', 'bar');
        $converter = new NeConverter();

        $this->assertEquals(
            (object) ['$ne' => ['foo', 'bar']],
            $converter->encode($operator)
        );
    }

    public function testSupports()
    {
        $converter = new NeConverter();

        $this->assertTrue($converter->canEncode(new Ne([], [])));
        $this->assertFalse($converter->canEncode('foo'));
    }
}
