<?php

namespace MongoDB\Options;

use MongoDB\Driver\WriteConcern;
use MongoDB\Exception\InvalidArgumentException;

interface SupportsWriteConcern
{
    public function getWriteConcern(): ?WriteConcern;

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withWriteConcern(?WriteConcern $writeConcern, bool $overwrite = true): self;
}
