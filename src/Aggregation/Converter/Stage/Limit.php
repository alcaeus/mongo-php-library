<?php

namespace MongoDB\Aggregation\Converter\Stage;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\Stage\Limit as LimitStage;

final class Limit extends AbstractConverter
{
    /**
     * @param mixed $value
     */
    public function supports($value): bool
    {
        return $value instanceof LimitStage;
    }

    /**
     * @param LimitStage $value
     */
    public function convert($value): object
    {
        return (object) [
            '$limit' => $this->encodeWithLibraryIfSupported($value->getLimit()),
        ];
    }
}
