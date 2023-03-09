<?php

namespace MongoDB\Aggregation\Converter;

interface Converter
{
    /** @param mixed $expression */
    public function supports($expression): bool;

    /**
     * @param mixed $expression
     * @return mixed
     */
    public function convert($expression);
}
