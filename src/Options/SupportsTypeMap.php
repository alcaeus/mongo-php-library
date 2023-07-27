<?php

namespace MongoDB\Options;

use MongoDB\Exception\InvalidArgumentException;

interface SupportsTypeMap
{
    public function getTypeMap(): ?array;

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withTypeMap(?array $typeMap, bool $overwrite = true): self;
}
