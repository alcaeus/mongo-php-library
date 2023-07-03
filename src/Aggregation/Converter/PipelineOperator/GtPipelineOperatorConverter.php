<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Converter\PipelineOperator;

final class GtPipelineOperatorConverter extends \MongoDB\Aggregation\Converter\AbstractConverter
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
        return (object) [
                        '$gt' => [
        $this->encodeWithLibraryIfSupported($expression->getExpression1()),
        $this->encodeWithLibraryIfSupported($expression->getExpression2()),
        ]
                    ];
    }
}

