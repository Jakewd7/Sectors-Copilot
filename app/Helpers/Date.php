<?php

use Carbon\Carbon;

if (!function_exists('dateformat')) {
    function dateformat(?string $date): string
    {
        if (empty($date))
            return '-';
        try {
            return Carbon::parse($date)->locale('id')->translatedFormat('d F Y');
        } catch (\Throwable) {
            return '-';
        }
    }
}

if (!function_exists('datetimeformat')) {
    function datetimeformat(?string $datetime): string
    {
        if (empty($datetime))
            return '-';
        try {
            return Carbon::parse($datetime)->locale('id')->translatedFormat('d F Y H:i');
        } catch (\Throwable) {
            return '-';
        }
    }
}

if (!function_exists('dateformat_short')) {
    function dateformat_short(?string $date): string
    {
        if (empty($date))
            return '-';
        try {
            return Carbon::parse($date)->format('d/m/Y');
        } catch (\Throwable) {
            return '-';
        }
    }
}

if (!function_exists('datetimeformat_short')) {
    function datetimeformat_short(?string $datetime): string
    {
        if (empty($datetime))
            return '-';
        try {
            return Carbon::parse($datetime)->format('d/m/Y H:i');
        } catch (\Throwable) {
            return '-';
        }
    }
}

if (!function_exists('timeformat')) {
    function timeformat(?string $time): string
    {
        if (empty($time))
            return '-';
        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Throwable) {
            return '-';
        }
    }
}

if (!function_exists('dateformat_mysql')) {
    function dateformat_mysql(?string $date): ?string
    {
        if (empty($date))
            return null;
        try {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        } catch (\Throwable) {
            try {
                return Carbon::parse($date)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }
    }
}

if (!function_exists('datetimeformat_day')) {
    function datetimeformat_day(?string $datetime): string
    {
        if (empty($datetime))
            return '-';
        try {
            return Carbon::parse($datetime)->locale('id')->translatedFormat('l, d F Y H:i');
        } catch (\Throwable) {
            return '-';
        }
    }
}