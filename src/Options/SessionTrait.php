<?php

namespace MongoDB\Options;

use MongoDB\Driver\Session;
use MongoDB\Exception\InvalidArgumentException;

/** @internal */
trait SessionTrait
{
    private ?Session $session;

    public function getSession(): ?Session
    {
        return $this->session;
    }

    public function isInTransaction(): bool
    {
        return $this->session && $this->session->isInTransaction();
    }

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withSession(?Session $session, bool $overwrite = true): self
    {
        if (! $overwrite && $this->session !== null) {
            return $this;
        }

        $instance = clone $this;

        $instance->extractAndValidateSession(['session' => $session]);

        return $instance;
    }

    /** @throws InvalidArgumentException */
    private function extractAndValidateSession(array $options): void
    {
        if (isset($options['session']) && ! $options['session'] instanceof Session) {
            throw InvalidArgumentException::invalidType('"session" option', $options['session'], Session::class);
        }

        $this->session = $options['session'] ?? null;
    }
}
