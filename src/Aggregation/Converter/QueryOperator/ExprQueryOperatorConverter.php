<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Converter\QueryOperator;

final class ExprQueryOperatorConverter extends \MongoDB\Aggregation\Converter\AbstractConverter
{
    /**
     * @param mixed $expression
     */
    protected function supports($expression) : bool
    {
        return $expression instanceof \MongoDB\Aggregation\QueryOperator\ExprQueryOperator;
    }

    /**
     * @param mixed $expression
     */
    protected function convert($expression)
    {
        return (object) [
                        '$expr' => $this->encodeWithLibraryIfSupported($expression->getExpression())
                    ];
    }
}

