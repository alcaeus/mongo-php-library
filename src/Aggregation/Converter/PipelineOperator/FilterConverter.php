<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Converter\PipelineOperator;

final class FilterConverter extends \MongoDB\Aggregation\Converter\AbstractConverter
{
    /**
     * @param mixed $expression
     */
    protected function supports($expression) : bool
    {
        return $expression instanceof \MongoDB\Aggregation\PipelineOperator\Filter;
    }

    /**
     * @param mixed $expression
     */
    protected function convert($expression)
    {
        return (object) [
                        '$filter' =>                     (object) array_filter(
                                ['input' => $this->encodeWithLibraryIfSupported($expression->getInput()),
        'cond' => $this->encodeWithLibraryIfSupported($expression->getCond()),
        'as' => $this->encodeWithLibraryIfSupported($expression->getAs()),
        'limit' => $this->encodeWithLibraryIfSupported($expression->getLimit()),],
                                function ($value, $key): bool
                                {
                                    return !in_array($key, ['as', 'limit']) || $value !== null;
                                },
                                ARRAY_FILTER_USE_BOTH
                            )
                    ];
    }
}

