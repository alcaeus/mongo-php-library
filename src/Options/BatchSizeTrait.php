<?php

namespace MongoDB\Options;

use MongoDB\Exception\InvalidArgumentException;

use function is_integer;

/** @internal */
trait BatchSizeTrait
{
    private ?int $batchSize;

    public function getBatchSize(): ?int
    {
        return $this->batchSize;
    }

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withBatchSize(?int $batchSize, bool $overwrite = true): self
    {
        if (! $overwrite && $this->batchSize !== null) {
            return $this;
        }

        $instance = clone $this;

        $instance->extractAndValidateBatchSize(['batchSize' => $batchSize]);

        return $instance;
    }

    /** @throws InvalidArgumentException */
    private function extractAndValidateBatchSize(array $options): void
    {
        if (isset($options['batchSize']) && ! is_integer($options['batchSize'])) {
            throw InvalidArgumentException::invalidType('"batchSize" option', $options['batchSize'], 'integer');
        }

        $this->batchSize = $options['batchSize'] ?? null;
    }
}
