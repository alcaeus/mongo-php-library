<?php

namespace MongoDB\Aggregation\Stage;

use MongoDB\Aggregation\Stage;

final class Limit implements Stage
{
    /** @var int $limit */
    private $limit = null;

    public function __construct(int $limit)
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
