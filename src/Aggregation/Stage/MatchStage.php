<?php

namespace MongoDB\Aggregation\Stage;

use MongoDB\Aggregation\Expression\ResolvesToMatchExpression;
use MongoDB\Aggregation\Stage;

final class MatchStage implements Stage
{
    /** @var ResolvesToMatchExpression|array|object $matchExpr */
    private $matchExpr = null;

    /**
     * @param ResolvesToMatchExpression|array|object $matchExpr
     */
    public function __construct($matchExpr)
    {
        $this->matchExpr = $matchExpr;
    }

    /**
     * @return ResolvesToMatchExpression|array|object
     */
    public function getMatchExpr()
    {
        return $this->matchExpr;
    }
}
