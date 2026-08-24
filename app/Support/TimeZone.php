<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * All persisted datetimes are UTC. This helper converts them for display
 * based on the viewing user's timezone, and converts user input back to UTC.
 */
class TimeZone
{
    public const DEFAULT_FORMAT = 'M j, Y g:i A';

    public static function identifiers(): array
    {
        return \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    }

    public static function convert(?CarbonInterface $date, string $timezone): ?Carbon
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date->toDateTimeString(), 'UTC')->setTimezone($timezone ?: 'UTC');
    }

    public static function format(?CarbonInterface $date, string $timezone, string $format = self::DEFAULT_FORMAT): string
    {
        return static::convert($date, $timezone)?->format($format) ?? '—';
    }

    /**
     * ISO-8601 string including the user's offset, so FullCalendar renders
     * the event at the correct wall-clock time without client-side guessing.
     */
    public static function iso(?CarbonInterface $date, string $timezone): ?string
    {
        return static::convert($date, $timezone)?->toIso8601String();
    }

    /**
     * Interpret a naive datetime picked in the user's timezone and store it as UTC.
     */
    public static function toUtc(string|CarbonInterface|null $date, string $timezone): ?Carbon
    {
        if (! $date) {
            return null;
        }

        return Carbon::parse($date, $timezone ?: 'UTC')->utc();
    }
}
