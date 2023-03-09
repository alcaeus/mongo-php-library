<?php

namespace MongoDB\Aggregation\Stage;

final class Limit implements \MongoDB\Aggregation\Stage
{
    /**
     * @var int $limit
     */
    private $limit = null;

    /**
     * @param int $limit
     */
    public function __construct($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
}

