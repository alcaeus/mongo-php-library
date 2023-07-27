<?php

namespace MongoDB\Options;

use MongoDB\Driver\WriteConcern;
use MongoDB\Exception\InvalidArgumentException;

/** @internal */
trait WriteConcernTrait
{
    private ?WriteConcern $writeConcern;

    public function getWriteConcern(): ?WriteConcern
    {
        return $this->writeConcern;
    }

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withWriteConcern(?WriteConcern $writeConcern, bool $overwrite = true): self
    {
        if (! $overwrite && $this->writeConcern !== null) {
            return $this;
        }

        $instance = clone $this;

        $instance->extractAndValidateWriteConcern(['writeConcern' => $writeConcern]);

        return $instance;
    }

    /** @throws InvalidArgumentException */
    private function extractAndValidateWriteConcern(array $options): void
    {
        if (! isset($options['writeConcern'])) {
            $this->writeConcern = null;

            return;
        }

        if (! $options['writeConcern'] instanceof WriteConcern) {
            throw InvalidArgumentException::invalidType('"writeConcern" option', $options['writeConcern'], WriteConcern::class);
        }

        if ($options['writeConcern']->isDefault()) {
            $this->writeConcern = null;

            return;
        }

        $this->writeConcern = $options['writeConcern'];
    }
}
