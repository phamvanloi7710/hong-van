<?php

namespace App\Support;

final class CsvCell
{
    public static function sanitize(mixed $value): string
    {
        $text = (string) ($value ?? '');

        return preg_match('/^[\t\r\n ]*[=+\-@]/', $text) === 1 ? "'".$text : $text;
    }
}
