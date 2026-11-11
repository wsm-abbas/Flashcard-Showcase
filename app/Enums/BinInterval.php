<?php

namespace App\Enums;

enum BinInterval: int
{
    case BIN_0 = 0;        // New cards (immediate)
    case BIN_1 = 5;        // 5 seconds
    case BIN_2 = 25;       // 25 seconds
    case BIN_3 = 120;      // 2 minutes
    case BIN_4 = 600;      // 10 minutes
    case BIN_5 = 3600;     // 1 hour
    case BIN_6 = 18000;    // 5 hours
    case BIN_7 = 86400;    // 1 day
    case BIN_8 = 432000;   // 5 days
    case BIN_9 = 2160000;  // 25 days
    case BIN_10 = 10368000; // 4 months
    case BIN_11 = -1;      // Never (mastered) - using -1 to represent null

    /**
     * Get the interval in seconds for a given bin number
     */
    public static function getInterval(int $bin): ?int
    {
        return match ($bin) {
            0 => self::BIN_0->value,
            1 => self::BIN_1->value,
            2 => self::BIN_2->value,
            3 => self::BIN_3->value,
            4 => self::BIN_4->value,
            5 => self::BIN_5->value,
            6 => self::BIN_6->value,
            7 => self::BIN_7->value,
            8 => self::BIN_8->value,
            9 => self::BIN_9->value,
            10 => self::BIN_10->value,
            11 => self::BIN_11->value,
            default => null,
        };
    }

    /**
     * Get all intervals as an array
     */
    public static function getAllIntervals(): array
    {
        return [
            0 => self::BIN_0->value,
            1 => self::BIN_1->value,
            2 => self::BIN_2->value,
            3 => self::BIN_3->value,
            4 => self::BIN_4->value,
            5 => self::BIN_5->value,
            6 => self::BIN_6->value,
            7 => self::BIN_7->value,
            8 => self::BIN_8->value,
            9 => self::BIN_9->value,
            10 => self::BIN_10->value,
            11 => self::BIN_11->value,
        ];
    }

    /**
     * Get the display name for a bin
     */
    public static function getBinName(int $bin): string
    {
        return match ($bin) {
            0 => 'Bin 0 (New)',
            1 => 'Bin 1 (5 seconds)',
            2 => 'Bin 2 (25 seconds)',
            3 => 'Bin 3 (2 minutes)',
            4 => 'Bin 4 (10 minutes)',
            5 => 'Bin 5 (1 hour)',
            6 => 'Bin 6 (5 hours)',
            7 => 'Bin 7 (1 day)',
            8 => 'Bin 8 (5 days)',
            9 => 'Bin 9 (25 days)',
            10 => 'Bin 10 (4 months)',
            11 => 'Bin 11 (Never - Mastered)',
            default => "Bin {$bin}",
        };
    }

    /**
     * Check if a bin represents "never" (mastered)
     */
    public static function isNever(int $bin): bool
    {
        return $bin === 11;
    }
}
