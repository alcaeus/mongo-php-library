<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Stage;

final class SortStage implements \MongoDB\Aggregation\Stage
{
    /**
     * @var \MongoDB\Aggregation\Expression\ResolvesToSortSpecification|array|object
     * $sortSpecification
     */
    private $sortSpecification;

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

