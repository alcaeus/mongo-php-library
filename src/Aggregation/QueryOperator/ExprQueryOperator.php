<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\QueryOperator;

final class ExprQueryOperator
{
    /**
     * @var
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression
     */
    private $expression;

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression
     */
    public function __construct($expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     */
    public function getExpression()
    {
        return $this->expression;
    }
}

