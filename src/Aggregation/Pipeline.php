<?php

namespace MongoDB\Aggregation;

use function array_unshift;

class Pipeline
{
    private $stages = [];

    /** @var Stage|Pipeline */
    public function __construct(...$stages)
    {
        array_map(
            function ($value)
            {
                if ($value instanceof Stage) {
                    $this->appendStage($value);
                }

                if ($value instanceof Pipeline) {
                    array_map([$this, 'appendStage'], $value->stages);
                }
            },
            $stages
        );
    }

    public function appendStage(Stage $stage)
    {
        $this->stages[] = $stage;
    }

    public function getStages(): array
    {
        return $this->stages;
    }

    public function prependStage(Stage $stage)
    {
        array_unshift($this->stages, $stage);
    }
}
