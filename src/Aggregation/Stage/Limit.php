<?php

namespace MongoDB\Aggregation\Stage;

use MongoDB\Aggregation\Stage;

final class Limit extends Stage
{
    private int $limit = null;

    public function __construct(int $limit)
    {
        $this->limit = $limit;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
