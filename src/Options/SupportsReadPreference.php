<?php

namespace MongoDB\Options;

use MongoDB\Driver\ReadPreference;
use MongoDB\Exception\InvalidArgumentException;

interface SupportsReadPreference
{
    public function getReadPreference(): ?ReadPreference;

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withReadPreference(?ReadPreference $readPreference, bool $overwrite = true): self;
}
