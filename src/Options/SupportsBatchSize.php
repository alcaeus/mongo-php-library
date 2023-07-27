<?php

namespace MongoDB\Options;

use MongoDB\Exception\InvalidArgumentException;

interface SupportsBatchSize
{
    public function getBatchSize(): ?int;

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withBatchSize(?int $batchSize, bool $overwrite = true): self;
}
