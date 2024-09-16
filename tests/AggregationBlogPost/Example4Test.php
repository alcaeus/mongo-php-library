<?php

namespace MongoDB\Tests\AggregationBlogPost;

use MongoDB\BSON\Document;
use MongoDB\BSON\Serializable;
use MongoDB\Builder\Accumulator;
use MongoDB\Builder\Expression;
use MongoDB\Builder\Pipeline;
use MongoDB\Builder\Query;
use MongoDB\Builder\Stage;
use MongoDB\Builder\Type\AccumulatorInterface;
use MongoDB\Tests\TestCase;
use stdClass;
use function MongoDB\object;

class Example4Test extends TestCase
{
    public function testFirstPipeline(): void
    {
        $pipeline = new Pipeline(
            Stage::lookup(
                as: 'movie',
                from: 'movies',
                localField: 'movie_id',
                foreignField: '_id',
            ),
            Stage::unwind(Expression::arrayFieldPath('movie')),
            Stage::replaceRoot(object(
                comment_id: Expression::objectIdFieldPath('_id'),
                user: Expression::stringFieldPath('name'),
                movie_id: Expression::objectIdFieldPath('movie_id'),
                movie_title: Expression::stringFieldPath('movie.title'),
                genres: Expression::arrayFieldPath('movie.genres'),
                rating: Expression::intFieldPath('movie.imdb.rating'),
                comment: Expression::stringFieldPath('text'),
            )),
            Stage::unwind(Expression::arrayFieldPath('genres')),
            Stage::group(
                _id: object(
                    user: Expression::stringFieldPath('user'),
                    genre: Expression::stringFieldPath('genres'),
                ),
                totalComments: Accumulator::sum(1),
                averageRating: Accumulator::avg(Expression::intFieldPath('rating')),
            ),
            Stage::group(
                _id: Expression::stringFieldPath('_id.user'),
                preferences: Accumulator::push(object(
                    genre: Expression::stringFieldPath('_id.genre'),
                    totalComments: Expression::intFieldPath('totalComments'),
                    averageRating: Expression::round(Expression::avg(Expression::doubleFieldPath('averageRating')), 2),
                )),
                totalComments: Accumulator::sum(Expression::intFieldPath('totalComments')),
            ),
            Stage::sort(totalComments: -1),
        );
    }

    public function testExtractLookupStage(): void
    {
        $pipeline = new Pipeline(
            $this->lookupMovie(),
            Stage::unwind(Expression::arrayFieldPath('movie')),
            Stage::replaceRoot(object(
                comment_id: Expression::objectIdFieldPath('_id'),
                user: Expression::stringFieldPath('name'),
                movie_id: Expression::objectIdFieldPath('movie_id'),
                movie_title: Expression::stringFieldPath('movie.title'),
                genres: Expression::arrayFieldPath('movie.genres'),
                rating: Expression::intFieldPath('movie.imdb.rating'),
                comment: Expression::stringFieldPath('text'),
            )),
            Stage::unwind(Expression::arrayFieldPath('genres')),
            Stage::group(
                _id: object(
                    user: Expression::stringFieldPath('user'),
                    genre: Expression::stringFieldPath('genres'),
                ),
                totalComments: Accumulator::sum(1),
                averageRating: Accumulator::avg(Expression::intFieldPath('rating')),
            ),
            Stage::group(
                _id: Expression::stringFieldPath('_id.user'),
                preferences: Accumulator::push(object(
                    genre: Expression::stringFieldPath('_id.genre'),
                    totalComments: Expression::intFieldPath('totalComments'),
                    averageRating: Expression::round(Expression::avg(Expression::doubleFieldPath('averageRating')), 2),
                )),
                totalComments: Accumulator::sum(Expression::intFieldPath('totalComments')),
            ),
            Stage::sort(totalComments: -1),
        );
    }

    public function testExtractLookupSingleMovie(): void
    {
        $pipeline = new Pipeline(
            $this->lookupSingleMovie(),
            Stage::replaceRoot(object(
                comment_id: Expression::objectIdFieldPath('_id'),
                user: Expression::stringFieldPath('name'),
                movie_id: Expression::objectIdFieldPath('movie_id'),
                movie_title: Expression::stringFieldPath('movie.title'),
                genres: Expression::arrayFieldPath('movie.genres'),
                rating: Expression::intFieldPath('movie.imdb.rating'),
                comment: Expression::stringFieldPath('text'),
            )),
            Stage::unwind(Expression::arrayFieldPath('genres')),
            Stage::group(
                _id: object(
                    user: Expression::stringFieldPath('user'),
                    genre: Expression::stringFieldPath('genres'),
                ),
                totalComments: Accumulator::sum(1),
                averageRating: Accumulator::avg(Expression::intFieldPath('rating')),
            ),
            Stage::group(
                _id: Expression::stringFieldPath('_id.user'),
                preferences: Accumulator::push(object(
                    genre: Expression::stringFieldPath('_id.genre'),
                    totalComments: Expression::intFieldPath('totalComments'),
                    averageRating: Expression::round(Expression::avg(Expression::doubleFieldPath('averageRating')), 2),
                )),
                totalComments: Accumulator::sum(Expression::intFieldPath('totalComments')),
            ),
            Stage::sort(totalComments: -1),
        );
    }

    public function testExtractGroupByUserAndGenre(): void
    {
        $pipeline = new Pipeline(
            $this->lookupSingleMovie(),
            Stage::replaceRoot(object(
                comment_id: Expression::objectIdFieldPath('_id'),
                user: Expression::stringFieldPath('name'),
                movie_id: Expression::objectIdFieldPath('movie_id'),
                movie_title: Expression::stringFieldPath('movie.title'),
                genres: Expression::arrayFieldPath('movie.genres'),
                rating: Expression::intFieldPath('movie.imdb.rating'),
                comment: Expression::stringFieldPath('text'),
            )),
            Stage::unwind(Expression::arrayFieldPath('genres')),
            $this->groupByUserAndGenre(
                totalComments: Accumulator::sum(1),
                averageRating: Accumulator::avg(Expression::intFieldPath('rating')),
            ),
            Stage::group(
                _id: Expression::stringFieldPath('_id.user'),
                preferences: Accumulator::push(object(
                    genre: Expression::stringFieldPath('_id.genre'),
                    totalComments: Expression::intFieldPath('totalComments'),
                    averageRating: Expression::round(Expression::avg(Expression::doubleFieldPath('averageRating')), 2),
                )),
                totalComments: Accumulator::sum(Expression::intFieldPath('totalComments')),
            ),
            Stage::sort(totalComments: -1),
        );
    }

    private function lookupMovie(): Stage\LookupStage
    {
        return Stage::lookup(
            as: 'movie',
            from: 'movies',
            localField: 'movie_id',
            foreignField: '_id',
        );
    }

    private function lookupSingleMovie(): Pipeline
    {
        return new Pipeline(
            Stage::lookup(
                as: 'movie',
                from: 'movies',
                localField: 'movie_id',
                foreignField: '_id',
            ),
            Stage::unwind(Expression::arrayFieldPath('movie')),
        );
    }

    private function groupByUserAndGenre(Document|Serializable|AccumulatorInterface|stdClass|array ...$field): Stage\GroupStage
    {
        return Stage::group(
            ...$field,
            _id: object(
                user: Expression::stringFieldPath('user'),
                genre: Expression::stringFieldPath('genres'),
            ),
        );
    }
}
