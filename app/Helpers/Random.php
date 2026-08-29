<?php

use Illuminate\Support\Str;

if (!function_exists('generateRandomString')) {
    function generateRandomString(int $length = 10): string
    {
        return Str::random($length);
    }
}

if (!function_exists('generateRandomNumber')) {
    function generateRandomNumber(int $length = 10): string
    {
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= random_int(0, 9);
        }
        return $result;
    }
}

if (!function_exists('randomNumbers')) {
    function randomNumbers(int $length): string
    {
        return generateRandomNumber($length);
    }
}