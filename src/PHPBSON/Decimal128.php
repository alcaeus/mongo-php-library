<?php

namespace MongoDB\PHPBSON;

use MongoDB\BSON\Decimal128Interface;

use function addslashes;
use function array_fill;
use function intdiv;
use function max;
use function sprintf;
use function strlen;
use function unpack;

/**
 * BSON type for the Decimal128 floating-point format, which supports numbers with up to 34 decimal digits (i.e. significant digits) and an exponent range of −6143 to +6144.
 *
 * @link https://php.net/manual/en/class.mongodb-bson-decimal128.php
 */
final class Decimal128 implements Type, Decimal128Interface
{
    private const EXPONENT_BIAS = 6176;

    /**
     * Construct a new Decimal128
     *
     * @link https://php.net/manual/en/mongodb-bson-decimal128.construct.php
     * @param string $value A decimal string or 16-byte little-endian binary string
     */
    final public function __construct(private string $value = '')
    {
        // If 16 bytes provided, treat as raw little-endian Decimal128 binary
        if ($value !== '' && strlen($value) === 16) {
            $this->value = $this->bytesToString($value);
        }
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function toCanonicalExtendedJSON(): string
    {
        return sprintf('{"$numberDecimal": "%s"}', addslashes($this->__toString()));
    }

    public function toRelaxedExtendedJSON(): string
    {
        // Relaxed form for Decimal128 is the same representation (string) in this implementation
        return $this->toCanonicalExtendedJSON();
    }

    /**
     * Decode 16-byte little-endian Decimal128 binary to its decimal string representation.
     */
    private function bytesToString(string $bytes): string
    {
        // Unpack 4 little-endian unsigned 32-bit ints
        $parts = unpack('V4', $bytes);
        $low = $parts[1];
        $midl = $parts[2];
        $midh = $parts[3];
        $high = $parts[4];

        // Determine sign: top bit of the most significant 32-bit word
        $isNegative = (($high & 0x80000000) !== 0);

        // Combination field: bits 1-5 of the high word (bits 122..126 in overall)
        $combination = ($high >> 26) & 0x1f;

        // Special values
        if (($combination >> 3) === 0x3) {
            // combination values 30 => Infinity, 31 => NaN
            if ($combination === 30) {
                return $isNegative ? '-Infinity' : 'Infinity';
            }

            if ($combination === 31) {
                return 'NaN';
            }
            // else fall through
        }

        // Determine biased exponent and significandMsb
        if (($combination >> 3) === 0x3) {
            $biasedExponent = ($high >> 15) & 0x3fff;
            $significandMsb = 0x08 + (($high >> 14) & 0x01);
        } else {
            $significandMsb = ($high >> 14) & 0x07;
            $biasedExponent = ($high >> 17) & 0x3fff;
        }

        $exponent = $biasedExponent - self::EXPONENT_BIAS;

        // Build 128-bit significand parts (most significant first)
        // parts[0] = top 32 bits (combination+top of significand)
        $significand128 = [0, 0, 0, 0];
        $significand128[0] = ($high & 0x3fff) + (($significandMsb & 0x0f) << 14);
        $significand128[1] = $midh;
        $significand128[2] = $midl;
        $significand128[3] = $low;

        $isZero = ($significand128[0] === 0 && $significand128[1] === 0 && $significand128[2] === 0 && $significand128[3] === 0);

        // We'll compute digits for both zero and non-zero to match canonical rules
        // Convert 128-bit significand into base-10 digits using repeated division by 1e9
        $digits = array_fill(0, 36, 0);
        $parts128 = $significand128;
        for ($k = 3; $k >= 0; $k--) {
            $res = $this->divideu128($parts128);
            $parts128 = $res['quotient'];
            $least = $res['rem'];
            for ($j = 8; $j >= 0; $j--) {
                $digits[$k * 9 + $j] = $least % 10;
                $least = intdiv($least, 10);
            }
        }

        // Find first non-zero
        $index = 0;
        while ($index < 36 && $digits[$index] === 0) {
            $index++;
        }

        $significandDigits = $isZero ? 1 : 36 - $index;

        // scientific exponent
        $scientificExponent = $significandDigits - 1 + $exponent;

        $out = '';
        if ($isNegative) {
            $out .= '-';
        }

        // Decide format using the same checks as JS implementation
        if (($scientificExponent >= 34) || ($scientificExponent <= -7) || ($exponent > 0)) {
            // Scientific format
            if ($significandDigits > 34) {
                $out .= '0';
                if ($exponent > 0) {
                    $out .= 'E+' . $exponent;
                } elseif ($exponent < 0) {
                    $out .= 'E' . $exponent;
                }

                return $out;
            }

            // first digit (for zero, digits[$index] may be undefined, use '0')
            $firstDigit = ($isZero ? 0 : $digits[$index]);
            $out .= (string) $firstDigit;
            if (! $isZero) {
                $index++;
            }

            $remaining = $significandDigits - 1;
            if ($remaining > 0) {
                $out .= '.';
                for ($i = 0; $i < $remaining; $i++) {
                    $out .= (string) ($isZero ? 0 : $digits[$index++]);
                }
            }

            $out .= 'E';
            if ($scientificExponent > 0) {
                $out .= '+' . $scientificExponent;
            } else {
                $out .= (string) $scientificExponent;
            }

            return $out;
        }

        // Regular format
        if ($exponent >= 0) {
            // simply output all digits (or zeros when isZero)
            if ($isZero) {
                // repeat '0' significandDigits times (which is 1)
                for ($i = 0; $i < $significandDigits; $i++) {
                    $out .= '0';
                }

                return $out;
            }

            for ($i = 0; $i < $significandDigits; $i++) {
                $out .= (string) $digits[$index++];
            }

            return $out;
        }

        // exponent < 0
        $radixPosition = $significandDigits + $exponent;
        if ($radixPosition > 0) {
            for ($i = 0; $i < $radixPosition; $i++) {
                $out .= $isZero ? '0' : (string) $digits[$index++];
            }
        } else {
            $out .= '0';
        }

        $out .= '.';
        while ($radixPosition++ < 0) {
            $out .= '0';
        }

        // remaining digits
        $remaining = $significandDigits - max($radixPosition - 1, 0);
        for ($i = 0; $i < $remaining; $i++) {
            $out .= $isZero ? '0' : (string) $digits[$index++];
        }

        return $out;
    }

    /**
     * Divide a 128-bit unsigned value represented as an array of four 32-bit words
     * by 1e9; return ['quotient' => array(4), 'rem' => int]
     */
    private function divideu128(array $value): array
    {
        $DIVISOR = 1000000000; // 1e9
        $rem = 0;
        $quotient = [0, 0, 0, 0];

        for ($i = 0; $i <= 3; $i++) {
            // rem is up to < 1e9 at each step, shift left 32 bits then add next part
            $rem = ($rem << 32) + ($value[$i] & 0xffffffff);
            // quotient part
            $q = intdiv($rem, $DIVISOR);
            $quotient[$i] = $q;
            $rem %= $DIVISOR;
        }

        return ['quotient' => $quotient, 'rem' => $rem];
    }
}
