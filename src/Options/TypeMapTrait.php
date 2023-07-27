<?php

namespace MongoDB\Options;

use MongoDB\Exception\InvalidArgumentException;

use function is_array;

/** @internal */
trait TypeMapTrait
{
    private ?array $typeMap;

    public function getTypeMap(): ?array
    {
        return $this->typeMap;
    }

    /**
     * @return static
     *
     * @throws InvalidArgumentException
     */
    public function withTypeMap(?array $typeMap, bool $overwrite = true): self
    {
        if (! $overwrite && $this->typeMap !== null) {
            return $this;
        }

        $instance = clone $this;

        $instance->extractAndValidateTypeMap(['typeMap' => $typeMap]);

        return $instance;
    }

    /** @throws InvalidArgumentException */
    private function extractAndValidateTypeMap(array $options): void
    {
        if (isset($options['typeMap']) && ! is_array($options['typeMap'])) {
            throw InvalidArgumentException::invalidType('"typeMap" option', $options['typeMap'], 'array');
        }

        $this->typeMap = $options['typeMap'] ?? null;
    }
}
