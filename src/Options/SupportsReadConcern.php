<?php

namespace MongoDB\Options;

use MongoDB\Driver\ReadConcern;
use MongoDB\Exception\InvalidArgumentException;

interface SupportsReadConcern
{
    public function getReadConcern(): ?ReadConcern;

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withReadConcern(?ReadConcern $readConcern, bool $overwrite = true): self;
}
