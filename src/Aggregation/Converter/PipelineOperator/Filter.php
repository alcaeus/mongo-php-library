<?php

namespace MongoDB\Aggregation\Converter\PipelineOperator;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\PipelineOperator\Filter as FilterOperator;

use function array_filter;

final class Filter extends AbstractConverter
{
    /**
     * @param mixed $value
     */
    public function supports($value): bool
    {
        return $value instanceof FilterOperator;
    }

    /**
     * @param FilterOperator $value
     */
    public function convert($value): object
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
