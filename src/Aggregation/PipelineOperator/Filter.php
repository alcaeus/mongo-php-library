<?php

namespace MongoDB\Aggregation\PipelineOperator;

final class Filter
{
    /**
     * @var
     * \MongoDB\Aggregation\Expression\ResolvesToArrayExpression|string|array|object
     * $input
     */
    private $input = null;

    /**
     * @var
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|bool|string|array|object
     * $cond
     */
    private $cond = null;

    /**
     * @var string $as
     */
    private $as = null;

    /**
     * @var
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|int|float|string|array|object
     * $limit
     */
    private $limit = null;

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToArrayExpression|string|array|object
     * $input
     * @param
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|bool|string|array|object
     * $cond
     * @param string $as
     * @param
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|int|float|string|array|object
     * $limit
     */
    public function __construct($input, $cond, $as, $limit)
    {
        $this->input = $input;
        $this->cond = $cond;
        $this->as = $as;
        $this->limit = $limit;
    }

    /**
     * @return
     * \MongoDB\Aggregation\Expression\ResolvesToArrayExpression|string|array|object
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|bool|string|array|object
     */
    public function getCond()
    {
        return $this->cond;
    }

    /**
     * @return string
     */
    public function getAs()
    {
        return $this->as;
    }

    /**
     * @return
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|int|float|string|array|object
     */
    public function getLimit()
    {
        return $this->limit;
    }
}

