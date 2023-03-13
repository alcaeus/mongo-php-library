<?php

namespace MongoDB\Aggregation\Converter\PipelineOperator;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\PipelineOperator\Filter;

use function array_filter;

final class FilterConverter extends AbstractConverter
{
    /**
     * @param mixed $value
     */
    protected function supports($value): bool
    {
        return $value instanceof Filter;
    }

    /**
     * @param Filter $value
     */
    protected function convert($value): object
    {
        $args = [
            'input' => $this->encodeWithLibraryIfSupported($value->getInput()),
            'cond' => $this->encodeWithLibraryIfSupported($value->getCond()),
            'as' => $this->encodeWithLibraryIfSupported($value->getAs()),
            'limit' => $this->encodeWithLibraryIfSupported($value->getLimit()),
        ];

        return (object) [
            '$filter' => (object) array_filter($args, function ($arg) {
                return $arg !== null;
            }),
        ];
    }
}
