<?php

namespace MongoDB\Aggregation\Converter\QueryOperator;

final class GtQueryOperatorConverter extends \MongoDB\Aggregation\Converter\AbstractConverter
{
    /**
     * @param mixed $expression
     */
    protected function supports($expression) : bool
    {
        return $expression instanceof \MongoDB\Aggregation\PipelineOperator\GtPipelineOperator;
    }

    /**
     * @param mixed $expression
     */
    protected function convert($expression)
    {
        return [
            $expression->getExpression1() => ['$gt' => $expression->getExpression2()]
        ];
    }
}

