<?php

namespace MongoDB\Aggregation\Converter\QueryOperator;

final class LtQueryOperatorConverter extends \MongoDB\Aggregation\Converter\AbstractConverter
{
    /**
     * @param mixed $expression
     */
    protected function supports($expression) : bool
    {
        return $expression instanceof \MongoDB\Aggregation\PipelineOperator\LtPipelineOperator;
    }

    /**
     * @param mixed $expression
     */
    protected function convert($expression)
    {
        return [
            $expression->getExpression1() => ['$lt' => $expression->getExpression2()]
        ];
    }
}

