<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\FactoryFunctions\QueryOperator;

    /**
     * @param \MongoDB\Aggregation\Expression\ResolvesToQuery|array|object $query
     */
    function andQueryOperator(... $query) : \MongoDB\Aggregation\QueryOperator\AndQueryOperator
    {
        return new \MongoDB\Aggregation\QueryOperator\AndQueryOperator(...func_get_args());
    }


    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression
     */
    function expr($expression) : \MongoDB\Aggregation\QueryOperator\ExprQueryOperator
    {
        return new \MongoDB\Aggregation\QueryOperator\ExprQueryOperator(...func_get_args());
    }
