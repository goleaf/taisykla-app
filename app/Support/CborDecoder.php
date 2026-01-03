<?php

namespace App\Support;

use RuntimeException;

final class CborDecoder
{
    public static function decode(string $data): mixed
    {
        $offset = 0;
        return self::decodeItem($data, $offset);
    }

    private static function decodeItem(string $data, int &$offset): mixed
    {
        if ($offset >= strlen($data)) {
            throw new RuntimeException('CBOR decode error: unexpected end of data.');
        }

        $initial = ord($data[$offset]);
        $offset += 1;

        $major = $initial >> 5;
        $info = $initial & 0x1f;
        $length = self::decodeLength($data, $offset, $info);

        return match ($major) {
            0 => $length,
            1 => -1 - $length,
            2 => self::readBytes($data, $offset, $length),
            3 => self::readText($data, $offset, $length),
            4 => self::readArray($data, $offset, $length),
            5 => self::readMap($data, $offset, $length),
            6 => self::decodeItem($data, $offset),
            7 => self::decodeSimple($data, $offset, $info),
            default => throw new RuntimeException('CBOR decode error: unknown major type.'),
        };
    }

    private static function decodeLength(string $data, int &$offset, int $info): int
    {
        if ($info < 24) {
            return $info;
        }

        return match ($info) {
            24 => self::readInt($data, $offset, 1),
            25 => self::readInt($data, $offset, 2),
            26 => self::readInt($data, $offset, 4),
            27 => self::readInt($data, $offset, 8),
            default => throw new RuntimeException('CBOR decode error: unsupported length.'),
        };
    }

    private static function readBytes(string $data, int &$offset, int $length): string
    {
        $chunk = substr($data, $offset, $length);
        if (strlen($chunk) !== $length) {
            throw new RuntimeException('CBOR decode error: invalid byte length.');
        }
        $offset += $length;

        return $chunk;
    }

    private static function readText(string $data, int &$offset, int $length): string
    {
        return self::readBytes($data, $offset, $length);
    }

    private static function readArray(string $data, int &$offset, int $length): array
    {
        $items = [];
        for ($i = 0; $i < $length; $i++) {
            $items[] = self::decodeItem($data, $offset);
        }

        return $items;
    }

    private static function readMap(string $data, int &$offset, int $length): array
    {
        $map = [];
        for ($i = 0; $i < $length; $i++) {
            $key = self::decodeItem($data, $offset);
            $value = self::decodeItem($data, $offset);
            if (is_int($key) || is_string($key)) {
                $map[$key] = $value;
            } else {
                $map[] = [$key, $value];
            }
        }

        return $map;
    }

    private static function decodeSimple(string $data, int &$offset, int $info): mixed
    {
        return match ($info) {
            20 => false,
            21 => true,
            22 => null,
            23 => null,
            24 => self::readInt($data, $offset, 1),
            25 => self::decodeHalfFloat(self::readInt($data, $offset, 2)),
            26 => self::decodeFloat($data, $offset),
            27 => self::decodeDouble($data, $offset),
            default => throw new RuntimeException('CBOR decode error: unsupported simple value.'),
        };
    }

    private static function readInt(string $data, int &$offset, int $bytes): int
    {
        $chunk = self::readBytes($data, $offset, $bytes);

        return match ($bytes) {
            1 => ord($chunk),
            2 => unpack('n', $chunk)[1],
            4 => unpack('N', $chunk)[1],
            8 => self::decodeUInt64($chunk),
            default => throw new RuntimeException('CBOR decode error: unsupported int size.'),
        };
    }

    private static function decodeUInt64(string $chunk): int
    {
        $parts = unpack('N2', $chunk);
        return ($parts[1] << 32) | $parts[2];
    }

    private static function decodeFloat(string $data, int &$offset): float
    {
        $chunk = self::readBytes($data, $offset, 4);
        return unpack('G', $chunk)[1];
    }

    private static function decodeDouble(string $data, int &$offset): float
    {
        $chunk = self::readBytes($data, $offset, 8);
        return unpack('E', $chunk)[1];
    }

    private static function decodeHalfFloat(int $half): float
    {
        $sign = ($half & 0x8000) ? -1 : 1;
        $exp = ($half >> 10) & 0x1f;
        $mant = $half & 0x03ff;

        if ($exp === 0) {
            return $sign * pow(2, -14) * ($mant / 1024);
        }

        if ($exp === 31) {
            return $mant === 0 ? $sign * INF : NAN;
        }

        return $sign * pow(2, $exp - 15) * (1 + ($mant / 1024));
    }
}
