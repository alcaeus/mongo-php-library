<?php

namespace MongoDB\Aggregation\Stage;

final class Sort implements \MongoDB\Aggregation\Stage
{
    /**
     * @var \MongoDB\Aggregation\Expression\ResolvesToSortSpecification|array|object
     * $sortSpecification
     */
    private $sortSpecification = null;

    /**
     * @param \MongoDB\Aggregation\Expression\ResolvesToSortSpecification|array|object
     * $sortSpecification
     */
    public function __construct($sortSpecification)
    {
        $this->sortSpecification = $sortSpecification;
    }

    /**
     * @return \MongoDB\Aggregation\Expression\ResolvesToSortSpecification|array|object
     */
    public function getSortSpecification()
    {
        return $this->sortSpecification;
    }
}

