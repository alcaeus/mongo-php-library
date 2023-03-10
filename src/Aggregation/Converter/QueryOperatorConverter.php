<?php
/**
 * THIS FILE IS AUTO-GENERATED. ANY CHANGES WILL BE LOST!
 */


namespace MongoDB\Aggregation\Converter;

final class QueryOperatorConverter extends \MongoDB\Codec\CodecLibrary
{
    public function __construct()
    {
        parent::__construct(new \MongoDB\Aggregation\Converter\QueryOperator\AndQueryOperatorConverter(), new \MongoDB\Aggregation\Converter\QueryOperator\ExprQueryOperatorConverter());
    }
}

