<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Converter;

final class StageConverter extends \MongoDB\Codec\CodecLibrary
{
    public function __construct()
    {
        parent::__construct(new \MongoDB\Aggregation\Converter\Stage\MatchStageConverter(), new \MongoDB\Aggregation\Converter\Stage\SortStageConverter(), new \MongoDB\Aggregation\Converter\Stage\LimitStageConverter());
    }
}

