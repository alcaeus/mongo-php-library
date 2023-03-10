<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Converter\QueryOperator;

final class AndQueryOperatorConverter extends \MongoDB\Aggregation\Converter\AbstractConverter
{
    /**
     * @param mixed $expression
     */
    protected function supports($expression) : bool
    {
        return $expression instanceof \MongoDB\Aggregation\QueryOperator\AndQueryOperator;
    }

    /**
     * @param mixed $expression
     */
    protected function convert($expression)
    {
        return (object) [
                        '$and' => array_map([$this, 'encodeWithLibraryIfSupported'], $expression->getQuery())
                    ];
    }
}

