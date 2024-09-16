<?php

namespace MongoDB\Tests\AggregationBlogPost;

use MongoDB\Builder\Accumulator;
use MongoDB\Builder\Expression;
use MongoDB\Builder\Pipeline;
use MongoDB\Builder\Stage;
use MongoDB\Tests\TestCase;

class Example1Test extends TestCase
{
    public function testExample1(): void
    {
        $pipeline = new Pipeline(
            Stage::group(
                _id: Expression::stringFieldPath('email'),
                totalComments: Accumulator::sum(1),
            ),
            Stage::sort(totalComments: -1),
            Stage::limit(5),
        );
    }
}
