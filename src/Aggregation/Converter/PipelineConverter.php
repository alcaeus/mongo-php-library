<?php

namespace MongoDB\Aggregation\Converter;

use MongoDB\Aggregation\Converter\AbstractConverter;
use MongoDB\Aggregation\Pipeline;
use MongoDB\Aggregation\Stage\LimitStage;
use MongoDB\Codec\CodecLibrary;
use MongoDB\Codec\KnowsCodecLibrary;

final class PipelineConverter extends AbstractConverter
{
    public function __construct($stageConverter = null)
    {
        $this->attachLibrary($stageConverter ?? new StageConverter());
    }

    /**
     * @param mixed $value
     */
    protected function supports($value): bool
    {
        return $value instanceof Pipeline;
    }

    /**
     * @param Pipeline $value
     */
    protected function convert($value): array
    {
        return array_map([$this, 'encodeWithLibraryIfSupported'], $value->getStages());
    }
}
