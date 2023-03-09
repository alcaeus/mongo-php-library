<?php

namespace MongoDB\Tests\Aggregation\Converter\PipelineOperator;

use Generator;
use MongoDB\Aggregation\Converter\PipelineOperator\Ne as NeConverter;
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
            $converter->convert($operator)
        );
    }

    public function testSupports()
    {
        $converter = new NeConverter();

        $this->assertTrue($converter->supports(new Ne([], [])));
        $this->assertFalse($converter->supports('foo'));
    }
}
