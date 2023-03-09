<?php

namespace MongoDB\Aggregation\Converter;

use MongoDB\Codec\Codec;
use MongoDB\Codec\CodecLibrary;
use MongoDB\Codec\KnowsCodecLibrary;
use MongoDB\Exception\UnexpectedValueException;
use function get_debug_type;

abstract class AbstractConverter implements Codec, KnowsCodecLibrary
{
    /** @var CodecLibrary|null */
    private $library = null;

    /** @param mixed $value */
    abstract protected function supports($value): bool;

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract protected function convert($value);

    final public function attachLibrary(CodecLibrary $library): void
    {
        $this->library = $library;
    }

    final public function canDecode($value): bool
    {
        return false;
    }

    final public function canEncode($value): bool
    {
        return $this->supports($value);
    }

    final public function decode($value)
    {
        throw new UnexpectedValueException(sprintf('"%s" can only encode, not decode', static::class));
    }

    final public function encode($value)
    {
        if (!$this->canEncode($value)) {
            throw new UnexpectedValueException(sprintf('"%s" can not convert value of type "%s"', static::class, get_debug_type($value)));
        }

        return $this->convert($value);
    }

    final protected function getLibrary(): ?CodecLibrary
    {
        return $this->library;
    }

    protected function encodeWithLibraryIfSupported($value)
    {
        return $this->library && $this->library->canEncode($value) ? $this->library->encode($value) : $value;
    }
}
