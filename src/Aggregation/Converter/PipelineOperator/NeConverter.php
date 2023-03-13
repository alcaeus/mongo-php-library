<?php

namespace MongoDB\Aggregation\Converter\PipelineOperator;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\PipelineOperator\Ne;

final class NeConverter extends AbstractConverter
{
    /**
     * @param mixed $value
     */
    protected function supports($value): bool
    {
        return $value instanceof Ne;
    }

    /**
     * @param Ne $value
     */
    protected function convert($value): object
    {
        return (object) [
            '$ne' => [
                $this->encodeWithLibraryIfSupported($value->getExpression1()),
                $this->encodeWithLibraryIfSupported($value->getExpression2()),
            ],
        ];
    }
}
