<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\QueryOperator;

final class AndQueryOperator
{
    /**
     * @var \MongoDB\Aggregation\Expression\ResolvesToQuery|array|object $query
     */
    private $query;

    /**
     * @param \MongoDB\Aggregation\Expression\ResolvesToQuery|array|object $query
     */
    public function __construct(... $query)
    {
        $this->query = $query;
    }

    /**
     * @return \MongoDB\Aggregation\Expression\ResolvesToQuery|array|object
     */
    public function getQuery()
    {
        return $this->query;
    }
}

