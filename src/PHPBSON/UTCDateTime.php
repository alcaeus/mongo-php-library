<?php

namespace MongoDB\PHPBSON;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use MongoDB\BSON\UTCDateTimeInterface;
use MongoDB\Exception\InvalidArgumentException;

use function is_string;
use function json_encode;
use function sprintf;
use function substr;

final class UTCDateTime implements UTCDateTimeInterface, Type
{
    // TODO: Store Int64 instance?
    public readonly int $milliseconds;
    private readonly DateTimeImmutable $dateTime;

    /**
     * The timestamp in milliseconds of 9999-12-31T23:59:59Z, aka the latest
     * date represented as ISO-8601 in relaxed extended JSON.
     */
    private const DEC_31st_9999 = 253_402_300_799_999;

    final public function __construct(int|string|float|DateTimeInterface|null $milliseconds = null)
    {
        if ($milliseconds === null) {
            $milliseconds = new DateTimeImmutable($milliseconds);
        }

        if ($milliseconds instanceof DateTimeInterface) {
            $this->dateTime = DateTimeImmutable::createFromInterface($milliseconds);
            $this->milliseconds = (int) $milliseconds->format('Uv');

            return;
        }

        if (is_string($milliseconds)) {
            // TODO: 64-bit handling
            $this->milliseconds = (int) $milliseconds;

            return;
        }

        $this->milliseconds = (int) $milliseconds;
        $this->dateTime = $this->createDateTime($milliseconds);
    }

    public function toDateTime(): DateTime
    {
        return DateTime::createFromImmutable($this->dateTime);
    }

    public function toDateTimeImmutable(): DateTimeImmutable
    {
        return $this->dateTime;
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
        if ($this->milliseconds < 0 || $this->milliseconds > self::DEC_31st_9999) {
            return $this->toCanonicalExtendedJSON();
        }

        // TODO PHP 8.4: Use getMicrosecond
        return (int) $this->dateTime->format('v')
            ? sprintf('{"$date" : %s}', json_encode($this->dateTime->format('Y-m-d\TH:i:s.vp')))
            : sprintf('{"$date" : %s}', json_encode($this->dateTime->format('Y-m-d\TH:i:sp')));
    }

    private function createDateTime(float|int|string $milliseconds): DateTimeImmutable
    {
        $millisecondString = sprintf('%04d', $milliseconds);

        $dateTime = DateTimeImmutable::createFromFormat('U v', substr($millisecondString, 0, -3) . ' ' . substr($millisecondString, -3));
        if (! $dateTime) {
            throw new InvalidArgumentException(sprintf('Invalid value for UTCDateTime given: %04d', $milliseconds));
        }

        return $dateTime;
    }
}
