<?php

namespace MongoDB\Aggregation\Converter\Stage;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\Converter\QueryOperator\GtQueryOperatorConverter;
use MongoDB\Aggregation\Converter\QueryOperator\LtQueryOperatorConverter;
use MongoDB\Aggregation\Stage\MatchStage;
use MongoDB\Codec\CodecLibrary;
use function array_map;
use function is_array;

final class MatchStageConverter extends AbstractConverter
{
    public function __construct()
    {
        $this->attachCodecLibrary(new CodecLibrary(
            new GtQueryOperatorConverter(),
            new LtQueryOperatorConverter(),
        ));
    }

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
        $matchExpression = $value->getMatchExpr();

        if (is_array($matchExpression) && array_is_list($matchExpression)) {
            $matchExpression = array_merge_recursive(
                ...array_map(
                    $this->encodeWithLibraryIfSupported(...),
                    $matchExpression,
                ),
            );
        } else {
            $matchExpression = $this->encodeWithLibraryIfSupported($matchExpression);
        }

        return (object) [
            '$match' => (object) $matchExpression,
        ];
    }
}
