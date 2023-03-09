<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Converter;

final class PipelineOperatorConverter extends \MongoDB\Codec\CodecLibrary
{
    public function __construct()
    {
        parent::__construct(new \MongoDB\Aggregation\Converter\PipelineOperator\EqPipelineOperatorConverter(), new \MongoDB\Aggregation\Converter\PipelineOperator\NePipelineOperatorConverter(), new \MongoDB\Aggregation\Converter\PipelineOperator\FilterPipelineOperatorConverter());
    }
}

