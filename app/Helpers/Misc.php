<?php

use Illuminate\Support\Str;

if (!function_exists('getallheaders')) {
    function getallheaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (str_starts_with($name, 'HTTP_')) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }
}

if (!function_exists('substrwords')) {
    function substrwords(?string $text, int $maxchar = 100, string $end = '...'): string
    {
        if (empty($text))
            return '';
        return Str::limit($text, $maxchar, $end);
    }
}

if (!function_exists('clearNum')) {
    function clearNum(string|int|float|null $number): int
    {
        if (empty($number))
            return 0;
        return (int) preg_replace('/[^\d]/', '', (string) $number);
    }
}

if (!function_exists('myNum')) {
    function myNum(int|float|string|null $number): string
    {
        if (empty($number) && $number !== 0 && $number !== '0')
            return '0';
        return number_format((float) $number, 0, ',', '.');
    }
}

if (!function_exists('format_code')) {
    function format_code(string|int|null $text, int $num_nol = 6): string
    {
        return str_pad((string) ($text ?? ''), $num_nol, '0', STR_PAD_LEFT);
    }
}