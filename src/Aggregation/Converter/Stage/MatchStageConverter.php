<?php

namespace MongoDB\Aggregation\Converter\Stage;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\Stage\MatchStage;

final class MatchStageConverter extends AbstractConverter
{
    /**
     * @param mixed $value
     */
    protected function supports($value): bool
    {
        return $value instanceof MatchStage;
    }

    /**
     * @param MatchStage $value
     */
    protected function convert($value): object
    {
        return (object) [
            '$match' => (object) $this->encodeWithLibraryIfSupported($value->getMatchExpr()),
        ];
    }
}
