<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\QueryOperator;

final class AndQueryOperator
{
    /**
     * @var \resolvesToQueryOperator $query
     */
    private $query;

    /**
     * @param \resolvesToQueryOperator $query
     */
    public function __construct(... $query)
    {
        $this->query = $query;
    }

    /**
     * @return \resolvesToQueryOperator
     */
    public function getQuery()
    {
        return $this->query;
    }
}

