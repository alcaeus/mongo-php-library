<?php

namespace MongoDB\PHPBSON;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use MongoDB\BSON\UTCDateTimeInterface;

final class UTCDateTime implements UTCDateTimeInterface, Type
{
    // TODO: Store Int64 instance?
    public readonly int $milliseconds;

    final public function __construct(int|string|float|DateTimeInterface|null $milliseconds = null)
    {
        if (is_string($milliseconds)) {
            // TODO: 64-bit handling
            $this->milliseconds = (int) $milliseconds;
            return;
        }

        if ($milliseconds === null) {
            $milliseconds = new DateTimeImmutable($milliseconds);
        }

        if ($milliseconds instanceof DateTimeInterface) {
            // TODO: yeah......about this code...
            $microseconds = $milliseconds->format('Uu');
            $milliseconds = substr($microseconds, -3);
            $this->milliseconds = (int) $milliseconds;
            return;
        }

        $this->milliseconds = (int) $milliseconds;
    }

    public function toDateTime(): DateTime
    {
        // TODO: Implement toDateTime() method.
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        // TODO: Implement toDateTimeImmutable() method.
    }

    public function __toString(): string
    {
        // TODO: Implement __toString() method.
    }

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf('{"$date" : {"$numberLong" : "%d"}}', $this->milliseconds);
    }

    public function toRelaxedExtendedJSON(): string
    {
        // TODO: Implement toRelaxedExtendedJSON() method.
    }
}
