<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Stage;

final class MatchStage implements \MongoDB\Aggregation\Stage
{
    /**
     * @var \MongoDB\Aggregation\Expression\ResolvesToMatchExpression|array|object
     * $matchExpr
     */
    private $matchExpr;

    /**
     * @param \MongoDB\Aggregation\Expression\ResolvesToMatchExpression|array|object
     * $matchExpr
     */
    public function __construct(... $matchExpr)
    {
        if (array_keys($matchExpr) === [0]) {
            $matchExpr = $matchExpr[0];
        }

        $this->matchExpr = $matchExpr;
    }

    /**
     * @return \MongoDB\Aggregation\Expression\ResolvesToMatchExpression|array|object
     */
    public function getMatchExpr()
    {
        return $this->matchExpr;
    }
}

