<?php

namespace MongoDB\Aggregation\Converter\PipelineOperator;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\PipelineOperator\Eq;

final class EqConverter extends AbstractConverter
{
    /**
     * @param mixed $value
     */
    protected function supports($value): bool
    {
        return $value instanceof Eq;
    }

    /**
     * @param Eq $value
     */
    protected function convert($value): object
    {
        return (object) [
            '$eq' => [
                $this->encodeWithLibraryIfSupported($value->getExpression1()),
                $this->encodeWithLibraryIfSupported($value->getExpression2()),
            ],
        ];
    }
}
