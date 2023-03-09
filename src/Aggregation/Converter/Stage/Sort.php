<?php

namespace MongoDB\Aggregation\Converter\Stage;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\Stage\Sort as SortStage;

final class Sort extends AbstractConverter
{
    /**
     * @param mixed $value
     */
    public function supports($value): bool
    {
        return $value instanceof SortStage;
    }

    /**
     * @param SortStage $value
     */
    public function convert($value): object
    {
        return (object) [
            '$sort' => $this->encodeWithLibraryIfSupported($value->getSortSpecification()),
        ];
    }
}
