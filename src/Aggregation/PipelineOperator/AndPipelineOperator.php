<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\PipelineOperator;

final class AndPipelineOperator
{
    /**
     * @var
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expressions
     */
    private $expressions;

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expressions
     */
    public function __construct(... $expressions)
    {
        $this->expressions = $expressions;
    }

    /**
     * @return
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     */
    public function getExpressions()
    {
        return $this->expressions;
    }
}

