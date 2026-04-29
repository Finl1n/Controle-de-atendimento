<?php

declare(strict_types=1);

final class Formatter
{
    public static function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function dateTime(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $date = new DateTimeImmutable($value);
        return $date->format('d/m/Y H:i');
    }

    public static function durationFromMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $restMinutes = $minutes % 60;

        return sprintf('%02dh %02dm', $hours, $restMinutes);
    }
}
