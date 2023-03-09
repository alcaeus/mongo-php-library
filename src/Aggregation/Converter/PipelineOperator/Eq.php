<?php

namespace MongoDB\Aggregation\Converter\PipelineOperator;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\PipelineOperator\Eq as EqOperator;

final class Eq extends AbstractConverter
{
    /**
     * @param mixed $value
     */
    public function supports($value): bool
    {
        return $value instanceof EqOperator;
    }

    /**
     * @param EqOperator $value
     */
    public function convert($value): object
    {
        return (object) [
            '$eq' => [
                $this->encodeWithLibraryIfSupported($value->getExpression1()),
                $this->encodeWithLibraryIfSupported($value->getExpression2()),
            ],
        ];
    }
}
