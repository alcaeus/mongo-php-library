<?php

namespace MongoDB\Options;

use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\UnsupportedException;
use stdClass;

use function is_array;
use function is_bool;
use function is_integer;
use function is_object;
use function is_string;
use function MongoDB\is_document;

final class AggregateOptions extends AbstractOptions implements
    SupportsBatchSize,
    SupportsReadConcern,
    SupportsReadPreference,
    SupportsSession,
    SupportsTypeMap,
    SupportsWriteConcern
{
    use BatchSizeTrait;
    use ReadConcernTrait;
    use ReadPreferenceTrait;
    use SessionTrait;
    use TypeMapTrait;
    use WriteConcernTrait;

    private ?bool $allowDiskUse;
    private ?bool $bypassDocumentValidation;

    /** @var array|object|null */
    private $collation;

    /** @var mixed */
    private $comment;

    private ?bool $explain;

    /** @var string|array|object|null */
    private $hint;

    /** @var array|object|null */
    private $let;

    private ?int $maxAwaitTimeMS;
    private ?int $maxTimeMS;
    private ?array $typeMap;

    public static function fromArray(array $options): self
    {
        if (isset($options['allowDiskUse']) && ! is_bool($options['allowDiskUse'])) {
            throw InvalidArgumentException::invalidType('"allowDiskUse" option', $options['allowDiskUse'], 'boolean');
        }

        if (isset($options['bypassDocumentValidation']) && ! is_bool($options['bypassDocumentValidation'])) {
            throw InvalidArgumentException::invalidType('"bypassDocumentValidation" option', $options['bypassDocumentValidation'], 'boolean');
        }

        if (isset($options['collation']) && ! is_document($options['collation'])) {
            throw InvalidArgumentException::expectedDocumentType('"collation" option', $options['collation']);
        }

        if (isset($options['explain']) && ! is_bool($options['explain'])) {
            throw InvalidArgumentException::invalidType('"explain" option', $options['explain'], 'boolean');
        }

        if (isset($options['hint']) && ! is_string($options['hint']) && ! is_array($options['hint']) && ! is_object($options['hint'])) {
            throw InvalidArgumentException::invalidType('"hint" option', $options['hint'], 'string or array or object');
        }

        if (isset($options['let']) && ! is_document($options['let'])) {
            throw InvalidArgumentException::expectedDocumentType('"let" option', $options['let']);
        }

        if (isset($options['maxAwaitTimeMS']) && ! is_integer($options['maxAwaitTimeMS'])) {
            throw InvalidArgumentException::invalidType('"maxAwaitTimeMS" option', $options['maxAwaitTimeMS'], 'integer');
        }

        if (isset($options['maxTimeMS']) && ! is_integer($options['maxTimeMS'])) {
            throw InvalidArgumentException::invalidType('"maxTimeMS" option', $options['maxTimeMS'], 'integer');
        }

        if (isset($options['bypassDocumentValidation']) && ! $options['bypassDocumentValidation']) {
            unset($options['bypassDocumentValidation']);
        }

        $instance = parent::fromArray($options);

        $instance->allowDiskUse = $options['allowDiskUse'] ?? null;
        $instance->bypassDocumentValidation = $options['bypassDocumentValidation'] ?? null;
        $instance->collation = $options['collation'] ?? null;
        $instance->comment = $options['comment'] ?? null;
        $instance->explain = $options['explain'] ?? null;
        $instance->hint = $options['hint'] ?? null;
        $instance->let = $options['let'] ?? null;
        $instance->maxAwaitTimeMS = $options['maxAwaitTimeMS'] ?? null;
        $instance->maxTimeMS = $options['maxTimeMS'] ?? null;
        $instance->readPreference = $options['readPreference'] ?? null;

        return $instance;
    }

    public function appendAggregateOptions(array $command): array
    {
        foreach (['allowDiskUse', 'bypassDocumentValidation', 'comment', 'explain', 'maxTimeMS'] as $option) {
            if (isset($this->$option)) {
                $command[$option] = $this->$option;
            }
        }

        foreach (['collation', 'let'] as $option) {
            if (isset($this->$option)) {
                $command[$option] = (object) $this->$option;
            }
        }

        if (isset($this->hint)) {
            $command['hint'] = is_array($this->hint) ? (object) $this->hint : $this->hint;
        }

        $command['cursor'] = isset($this->batchSize)
            ? ['batchSize' => $this->batchSize]
            : new stdClass();

        return $command;
    }

    /** @throws UnsupportedException */
    public function createCommandExecutionOptions(): array
    {
        $inTransaction = isset($this->session) && $this->session->isInTransaction();
        if ($inTransaction) {
            if (isset($this->readConcern)) {
                throw UnsupportedException::readConcernNotSupportedInTransaction();
            }

            if (isset($this->writeConcern)) {
                throw UnsupportedException::writeConcernNotSupportedInTransaction();
            }
        }

        $options = [];

        foreach (['readConcern', 'readPreference', 'session', 'writeConcern'] as $option) {
            if (isset($this->$option)) {
                $options[$option] = $this->$option;
            }
        }

        return $options;
    }

    public function createCommandOptions(): array
    {
        $cmdOptions = [];

        if (isset($this->maxAwaitTimeMS)) {
            $cmdOptions['maxAwaitTimeMS'] = $this->maxAwaitTimeMS;
        }

        return $cmdOptions;
    }

    public function isExplain(): bool
    {
        return $this->explain ?? false;
    }
}
