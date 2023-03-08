<?php

namespace MongoDB\Aggregation\Stage;

use MongoDB\Aggregation\Expression\ResolvesToMatchExpression;
use MongoDB\Aggregation\Stage;

final class Match extends Stage
{
    private ResolvesToMatchExpression|array|object $matchExpr = null;

    public function __construct(ResolvesToMatchExpression|array|object $matchExpr)
    {
        $this->matchExpr = $matchExpr;
    }

    public function getMatchExpr(): ResolvesToMatchExpression|array|object
    {
        return $this->matchExpr;
    }
}
