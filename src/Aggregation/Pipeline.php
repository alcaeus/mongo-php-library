<?php

namespace MongoDB\Aggregation;

use function array_unshift;

class Pipeline
{
    private $stages = [];

    public function __construct(Stage ...$stages)
    {
        $this->stages = $stages;
    }

    public function append(Stage $stage)
    {
        $this->stages[] = $stage;
    }

    public function prepend(Stage $stage)
    {
        array_unshift($this->stages, $stage);
    }
}
