<?php

namespace MongoDB\Aggregation\Converter\Stage;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\Stage\MatchStage as MatchStageData;

final class MatchStage extends AbstractConverter
{
    /**
     * @param mixed $value
     */
    public function supports($value): bool
    {
        return $value instanceof MatchStageData;
    }

    /**
     * @param MatchStageData $value
     */
    public function convert($value): object
    {
        return (object) [
            '$match' => (object) $this->encodeWithLibraryIfSupported($value->getMatchExpr()),
        ];
    }
}
