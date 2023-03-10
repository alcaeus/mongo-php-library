<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Factory;

class QueryOperatorFactory
{
    /**
     * @param \MongoDB\Aggregation\Expression\ResolvesToQuery|array|object $query
     */
    public static function and(... $query) : \MongoDB\Aggregation\QueryOperator\AndQueryOperator
    {
        return new \MongoDB\Aggregation\QueryOperator\AndQueryOperator(...func_get_args());
    }

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression
     */
    public static function expr($expression) : \MongoDB\Aggregation\QueryOperator\ExprQueryOperator
    {
        return new \MongoDB\Aggregation\QueryOperator\ExprQueryOperator(...func_get_args());
    }
}

