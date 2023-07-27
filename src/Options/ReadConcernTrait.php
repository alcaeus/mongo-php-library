<?php

namespace MongoDB\Options;

use MongoDB\Driver\ReadConcern;
use MongoDB\Exception\InvalidArgumentException;

/** @internal */
trait ReadConcernTrait
{
    private ?ReadConcern $readConcern;

    public function getReadConcern(): ?ReadConcern
    {
        return $this->readConcern;
    }

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withReadConcern(?ReadConcern $readConcern, bool $overwrite = true): self
    {
        if (! $overwrite && $this->readConcern !== null) {
            return $this;
        }

        $instance = clone $this;

        $instance->extractAndValidateReadConcern(['readConcern' => $readConcern]);

        return $instance;
    }

    /** @throws InvalidArgumentException */
    private function extractAndValidateReadConcern(array $options): void
    {
        if (! isset($options['readConcern'])) {
            $this->readConcern = null;

            return;
        }

        if (! $options['readConcern'] instanceof ReadConcern) {
            throw InvalidArgumentException::invalidType('"readConcern" option', $options['readConcern'], ReadConcern::class);
        }

        if ($options['readConcern']->isDefault()) {
            $this->readConcern = null;

            return;
        }

        $this->readConcern = $options['readConcern'];
    }
}
