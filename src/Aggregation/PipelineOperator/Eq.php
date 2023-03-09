<?php

namespace MongoDB\Aggregation\PipelineOperator;

use MongoDB\Aggregation\Expression\ResolvesToExpression;

final class Eq
{
    private ResolvesToExpression|bool|int|float|string|array|object|null $expression1 = null;

    private ResolvesToExpression|bool|int|float|string|array|object|null $expression2 = null;

    public function __construct(ResolvesToExpression|bool|int|float|string|array|object|null $expression1, ResolvesToExpression|bool|int|float|string|array|object|null $expression2)
    {
        $this->expression1 = $expression1;
        $this->expression2 = $expression2;
    }

    public function getExpression1(): ResolvesToExpression|bool|int|float|string|array|object|null
    {
        return $this->expression1;
    }

    public function getExpression2(): ResolvesToExpression|bool|int|float|string|array|object|null
    {
        return $this->expression2;
    }
}
