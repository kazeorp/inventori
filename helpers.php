<?php
// helpers.php

// Fungsi untuk keamanan (mencegah XSS)
if (!function_exists('e')) {
    function e($text) {
        return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
    }
}
// HINDARI TAG PENUTUP