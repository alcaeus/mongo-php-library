<?php

namespace MongoDB\Aggregation\PipelineOperator;

use MongoDB\Aggregation\Expression\ResolvesToArrayExpression;
use MongoDB\Aggregation\Generator\ResolvesToBoolExpression;

final class Filter
{
    private ResolvesToArrayExpression|string|array|object $input = null;

    private ResolvesToBoolExpression|bool|string|array|object $cond = null;

    private string $as = null;

    private ResolvesToBoolExpression|int|float|string|array|object $limit = null;

    public function __construct(ResolvesToArrayExpression|string|array|object $input, ResolvesToBoolExpression|bool|string|array|object $cond, string $as, ResolvesToBoolExpression|int|float|string|array|object $limit)
    {
        $this->input = $input;
        $this->cond = $cond;
        $this->as = $as;
        $this->limit = $limit;
    }

    public function getInput(): ResolvesToArrayExpression|string|array|object
    {
        return $this->input;
    }

    public function getCond(): ResolvesToBoolExpression|bool|string|array|object
    {
        return $this->cond;
    }

    public function getAs(): string
    {
        return $this->as;
    }

    public function getLimit(): ResolvesToBoolExpression|int|float|string|array|object
    {
        return $this->limit;
    }
}
