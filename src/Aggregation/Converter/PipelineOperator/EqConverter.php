<?php

namespace MongoDB\Aggregation\Converter\PipelineOperator;

final class EqConverter extends \MongoDB\Aggregation\Converter\AbstractConverter
{
    /**
     * @param mixed $expression
     */
    protected function supports($expression) : bool
    {
        return $expression instanceof \MongoDB\Aggregation\PipelineOperator\Eq;
    }

    /**
     * @param mixed $expression
     */
    protected function convert($expression)
    {
        return (object) [
                        '$eq' => [
        $this->encodeWithLibraryIfSupported($expression->getExpression1()),
        $this->encodeWithLibraryIfSupported($expression->getExpression2()),
        ]
                    ];
    }
}

