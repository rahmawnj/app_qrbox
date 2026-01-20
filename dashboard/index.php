<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 */

$uri = $_SERVER['REQUEST_URI'];

// Jika URI mengandung "/public", hapus bagian itu
if (strpos($uri, '/public') === 0) {
    $uri = substr($uri, 7); // Menghapus '/public'
}

require __DIR__ . '/public/index.php';