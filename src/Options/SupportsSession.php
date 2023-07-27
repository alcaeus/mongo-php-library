<?php

namespace MongoDB\Options;

use MongoDB\Driver\Session;
use MongoDB\Exception\InvalidArgumentException;

interface SupportsSession
{
    public function getSession(): ?Session;

    public function isInTransaction(): bool;

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withSession(?Session $session, bool $overwrite = true): self;
}
