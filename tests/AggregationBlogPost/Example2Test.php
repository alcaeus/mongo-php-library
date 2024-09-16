<?php

namespace MongoDB\Tests\AggregationBlogPost;

use MongoDB\Builder\Accumulator;
use MongoDB\Builder\Expression;
use MongoDB\Builder\Pipeline;
use MongoDB\Builder\Query;
use MongoDB\Builder\Stage;
use MongoDB\Tests\TestCase;
use function MongoDB\object;

class Example2Test extends TestCase
{
    public function testExample2(): void
    {
        $pipeline = new Pipeline(
            Stage::match(...[
                'imdb.rating' => Query::exists(),
            ]),
            Stage::addFields(
                month: Expression::month(Expression::dateFieldPath('released')),
                year: Expression::year(Expression::dateFieldPath('released')),
            ),
            Stage::group(
                _id: object(
                    year: Expression::intFieldPath('year'),
                    month: Expression::intFieldPath('month'),
                ),
                averageRating: Accumulator::avg(Expression::intFieldPath('imdb.rating')),
            ),
            Stage::sort(...[
                '_id.year' => 1,
                '_id.month' => 1,
            ]),
        );
    }
}
