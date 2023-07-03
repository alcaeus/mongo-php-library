<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Converter\PipelineOperator;

final class LtPipelineOperatorConverter extends \MongoDB\Aggregation\Converter\AbstractConverter
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
        return (object) [
                        '$lt' => [
        $this->encodeWithLibraryIfSupported($expression->getExpression1()),
        $this->encodeWithLibraryIfSupported($expression->getExpression2()),
        ]
                    ];
    }
}

