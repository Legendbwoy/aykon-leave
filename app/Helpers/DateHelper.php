<?php

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd M Y') {
        if ($date && $date instanceof \Carbon\Carbon) {
            return $date->format($format);
        }
        return 'N/A';
    }
}

if (!function_exists('formatTime')) {
    function formatTime($date, $format = 'H:i:s') {
        if ($date && $date instanceof \Carbon\Carbon) {
            return $date->format($format);
        }
        return '---';
    }
}

if (!function_exists('safeDateFormat')) {
    function safeDateFormat($date, $format = 'd M Y', $default = 'N/A') {
        if ($date && ($date instanceof \Carbon\Carbon || $date instanceof \DateTime)) {
            return $date->format($format);
        }
        return $default;
    }
}