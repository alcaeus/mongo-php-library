<?php

namespace MongoDB\Aggregation\PipelineOperator;

final class Ne
{
    /**
     * @var
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression1
     */
    private $expression1 = null;

    /**
     * @var
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression2
     */
    private $expression2 = null;

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression1
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     * $expression2
     */
    public function __construct($expression1, $expression2)
    {
        $this->expression1 = $expression1;
        $this->expression2 = $expression2;
    }

    /**
     * @return
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     */
    public function getExpression1()
    {
        return $this->expression1;
    }

    /**
     * @return
     * \MongoDB\Aggregation\Expression\ResolvesToExpression|bool|int|float|string|array|object|null
     */
    public function getExpression2()
    {
        return $this->expression2;
    }
}

