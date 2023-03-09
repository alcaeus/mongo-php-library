<?php

namespace MongoDB\Aggregation\Stage;

use MongoDB\Aggregation\Expression\ResolvesToSortSpecification;
use MongoDB\Aggregation\Stage;

final class Sort implements Stage
{
    /** @var ResolvesToSortSpecification|array|object $sortSpecification */
    private $sortSpecification = null;

    /**
     * @param ResolvesToSortSpecification|array|object $sortSpecification
     */
    public function __construct($sortSpecification)
    {
        $this->sortSpecification = $sortSpecification;
    }

    /**
     * @return ResolvesToSortSpecification|array|object
     */
    public function getSortSpecification()
    {
        return $this->sortSpecification;
    }
}
