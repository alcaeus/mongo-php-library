<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Factory;

class PipelineOperatorFactory
{
    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expressions
     */
    public static function and(... $expressions) : \MongoDB\Aggregation\PipelineOperator\AndPipelineOperator
    {
        return new \MongoDB\Aggregation\PipelineOperator\AndPipelineOperator(...func_get_args());
    }

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression1
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression2
     */
    public static function eq($expression1, $expression2) : \MongoDB\Aggregation\PipelineOperator\EqPipelineOperator
    {
        return new \MongoDB\Aggregation\PipelineOperator\EqPipelineOperator(...func_get_args());
    }

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression1
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression2
     */
    public static function gt($expression1, $expression2) : \MongoDB\Aggregation\PipelineOperator\GtPipelineOperator
    {
        return new \MongoDB\Aggregation\PipelineOperator\GtPipelineOperator(...func_get_args());
    }

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression1
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression2
     */
    public static function lt($expression1, $expression2) : \MongoDB\Aggregation\PipelineOperator\LtPipelineOperator
    {
        return new \MongoDB\Aggregation\PipelineOperator\LtPipelineOperator(...func_get_args());
    }

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression1
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression2
     */
    public static function ne($expression1, $expression2) : \MongoDB\Aggregation\PipelineOperator\NePipelineOperator
    {
        return new \MongoDB\Aggregation\PipelineOperator\NePipelineOperator(...func_get_args());
    }

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToArrayExpression|string|array|object
     * $input
     * @param
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|bool|string|array|object
     * $cond
     * @param string|null $as
     * @param
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|int|float|string|array|object|null
     * $limit
     */
    public static function filter($input, $cond, $as = null, $limit = null) : \MongoDB\Aggregation\PipelineOperator\FilterPipelineOperator
    {
        return new \MongoDB\Aggregation\PipelineOperator\FilterPipelineOperator(...func_get_args());
    }
}

