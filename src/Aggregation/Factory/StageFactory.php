<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Factory;

class StageFactory
{
    /**
     * @param \MongoDB\Aggregation\Expression\ResolvesToMatchExpression|array|object
     * $matchExpr
     */
    public static function match($matchExpr) : \MongoDB\Aggregation\Stage\MatchStage
    {
        return new \MongoDB\Aggregation\Stage\MatchStage(...func_get_args());
    }

    /**
     * @param \MongoDB\Aggregation\Expression\ResolvesToSortSpecification|array|object
     * $sortSpecification
     */
    public static function sort($sortSpecification) : \MongoDB\Aggregation\Stage\SortStage
    {
        return new \MongoDB\Aggregation\Stage\SortStage(...func_get_args());
    }

    /**
     * @param int $limit
     */
    public static function limit($limit) : \MongoDB\Aggregation\Stage\LimitStage
    {
        return new \MongoDB\Aggregation\Stage\LimitStage(...func_get_args());
    }
}

