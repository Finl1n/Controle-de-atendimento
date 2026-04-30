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

    public static function period(?string $start, ?string $end): string
    {
        if ($start === null || $start === '') {
            return '-';
        }

        $startDate = new DateTimeImmutable($start);
        $label = $startDate->format('d/m/Y H:i');

        if ($end === null || $end === '') {
            return $label . ' - em andamento';
        }

        $endDate = new DateTimeImmutable($end);
        return $label . ' → ' . $endDate->format('H:i');
    }

    public static function durationFromMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $restMinutes = $minutes % 60;

        if ($hours <= 0) {
            return sprintf('%dm', $restMinutes);
        }

        if ($restMinutes <= 0) {
            return sprintf('%dh', $hours);
        }

        return sprintf('%dh %dm', $hours, $restMinutes);
    }

    public static function multiline(?string $value): string
    {
        return nl2br(self::e($value));
    }
}
