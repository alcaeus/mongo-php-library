<?php

namespace MongoDB\Aggregation\Stage;

use MongoDB\Aggregation\Expression\ResolvesToSortSpecification;
use MongoDB\Aggregation\Stage;

final class Sort implements Stage
{
    /** @var ResolvesToSortSpecification|array|object $sortSpecificataion */
    private $sortSpecificataion = null;

    /**
     * @param ResolvesToSortSpecification|array|object $sortSpecificataion
     */
    public function __construct($sortSpecificataion)
    {
        $this->sortSpecificataion = $sortSpecificataion;
    }

    /**
     * @return ResolvesToSortSpecification|array|object
     */
    public function getSortSpecificataion()
    {
        return $this->sortSpecificataion;
    }
}
