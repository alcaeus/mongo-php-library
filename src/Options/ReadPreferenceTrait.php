<?php

namespace MongoDB\Options;

use MongoDB\Driver\ReadPreference;
use MongoDB\Exception\InvalidArgumentException;

/** @internal */
trait ReadPreferenceTrait
{
    private ?ReadPreference $readPreference;

    public function getReadPreference(): ?ReadPreference
    {
        return $this->readPreference;
    }

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withReadPreference(?ReadPreference $readPreference, bool $overwrite = true): self
    {
        if (! $overwrite && $this->readPreference !== null) {
            return $this;
        }

        $instance = clone $this;

        $instance->extractAndValidateReadPreference(['readPreference' => $readPreference]);

        return $instance;
    }

    /** @throws InvalidArgumentException */
    private function extractAndValidateReadPreference(array $options): void
    {
        if (isset($options['readPreference']) && ! $options['readPreference'] instanceof ReadPreference) {
            throw InvalidArgumentException::invalidType('"readPreference" option', $options['readPreference'], ReadPreference::class);
        }

        $this->readPreference = $options['readPreference'] ?? null;
    }
}
