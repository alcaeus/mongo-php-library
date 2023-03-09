<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\PipelineOperator;

final class FilterPipelineOperator
{
    /**
     * @var
     * \MongoDB\Aggregation\Expression\ResolvesToArrayExpression|string|array|object
     * $input
     */
    private $input;

    /**
     * @var
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|bool|string|array|object
     * $cond
     */
    private $cond;

    /**
     * @var string|null $as
     */
    private $as;

    /**
     * @var
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|int|float|string|array|object|null
     * $limit
     */
    private $limit;

    /**
     * @param
     * \MongoDB\Aggregation\Expression\ResolvesToArrayExpression|string|array|object
     * $input
     * @param
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|bool|string|array|object
     * $cond
     * @param string|null $as
     * @param
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|int|float|string|array|object|null
     * $limit
     */
    public function __construct($input, $cond, $as = null, $limit = null)
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
     * @return string|null
     */
    public function getAs()
    {
        return $this->as;
    }

    /**
     * @return
     * \MongoDB\Aggregation\Generator\ResolvesToBoolExpression|int|float|string|array|object|null
     */
    public function getLimit()
    {
        return $this->limit;
    }
}

