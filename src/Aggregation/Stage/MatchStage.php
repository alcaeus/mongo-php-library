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
    public function __construct($matchExpr)
    {
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

