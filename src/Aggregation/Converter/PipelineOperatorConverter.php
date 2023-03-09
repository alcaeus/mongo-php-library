<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Converter;

final class PipelineOperatorConverter extends \MongoDB\Codec\CodecLibrary
{
    public function __construct()
    {
        parent::__construct(new \MongoDB\Aggregation\Converter\PipelineOperator\EqConverter(), new \MongoDB\Aggregation\Converter\PipelineOperator\NeConverter(), new \MongoDB\Aggregation\Converter\PipelineOperator\FilterConverter());
    }
}

