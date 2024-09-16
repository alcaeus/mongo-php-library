<?php

namespace MongoDB\Tests\AggregationBlogPost;

use MongoDB\Builder\Accumulator;
use MongoDB\Builder\Expression;
use MongoDB\Builder\Pipeline;
use MongoDB\Builder\Query;
use MongoDB\Builder\Stage;
use MongoDB\Tests\TestCase;
use function MongoDB\object;

class Example3Test extends TestCase
{
    public function testExample3(): void
    {
        $pipeline = new Pipeline(
            Stage::lookup(
                as: 'comments',
                from: 'comments',
                localField: '_id',
                foreignField: 'movie_id',
            ),
            Stage::facet(
                mostCommentedMovies: new Pipeline(
                    Stage::project(
                        _id: 0,
                        title: 1,
                        commentCount: Expression::size(Expression::arrayFieldPath('comments')),
                    ),
                    Stage::sort(commentCount: -1),
                    Stage::limit(5),
                ),
                mostCommentedGenre: new Pipeline(
                    Stage::unwind(Expression::arrayFieldPath('genres')),
                    Stage::group(
                        _id: expression::stringFieldPath('genres'),
                        totalComments: Accumulator::sum(Expression::size(Expression::arrayFieldPath('comments'))),
                    ),
                    Stage::sort(totalComments: -1),
                    Stage::limit(5),
                ),
            ),
        );
    }
}
