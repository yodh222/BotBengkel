<?php
// config.php

// Base URL aplikasi (sesuaikan dengan environment Anda)
define('BASE_URL', 'http://localhost:8000/');
define('BASE_PATH', __DIR__); // atau __DIR__ . '/..' sesuai struktur folder Anda

// Konfigurasi database (jika aplikasi memerlukan akses database)
define('DB_HOST', 'localhost');
define('DB_NAME', 'databengkel');
define('DB_USER', 'root');
define('DB_PASS', '');

// Pengaturan error reporting (disarankan aktifkan saat development)
error_reporting(E_ALL);
ini_set('display_errors', 1);
