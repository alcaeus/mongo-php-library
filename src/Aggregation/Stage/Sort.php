<?php

namespace MongoDB\Aggregation\Stage;

use MongoDB\Aggregation\Expression\ResolvesToSortSpecification;
use MongoDB\Aggregation\Stage;

final class Sort extends Stage
{
    private ResolvesToSortSpecification|array|object $sortSpecificataion = null;

    public function __construct(ResolvesToSortSpecification|array|object $sortSpecificataion)
    {
        $this->sortSpecificataion = $sortSpecificataion;
    }

    public function getSortSpecificataion(): ResolvesToSortSpecification|array|object
    {
        return $this->sortSpecificataion;
    }
}
