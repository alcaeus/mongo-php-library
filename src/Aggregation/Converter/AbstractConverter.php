<?php

namespace MongoDB\Aggregation\Converter;

use MongoDB\Codec\CodecLibrary;
use MongoDB\Codec\EncodeIfSupported;
use MongoDB\Codec\Encoder;
use MongoDB\Codec\CodecLibraryAware;
use MongoDB\Exception\UnexpectedValueException;
use function get_debug_type;

abstract class AbstractConverter implements Encoder, CodecLibraryAware
{
    use EncodeIfSupported;

    /** @var CodecLibrary|null */
    private $library = null;

    /** @param mixed $value */
    abstract protected function supports($value): bool;

    /**
     * @param mixed $value
     * @return mixed
     */
    abstract protected function convert($value);

    final public function attachCodecLibrary(CodecLibrary $library): void
    {
        $this->library = $library;
    }

    final public function canEncode($value): bool
    {
        return $this->supports($value);
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
        return $this->library ? $this->library->encodeIfSupported($value) : $value;
    }
}
