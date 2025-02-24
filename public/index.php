<?php
// public/index.php

// Tampilkan error (untuk development)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Muat file konfigurasi
require_once __DIR__ . '/../config.php';

// Autoload (sederhana) untuk memuat kelas di folder core dan app/controllers
spl_autoload_register(function ($class) {
    // Cek di folder core
    if (file_exists(__DIR__ . '/../core/' . $class . '.php')) {
        require_once __DIR__ . '/../core/' . $class . '.php';
    }
    // Cek di folder app/controllers
    elseif (file_exists(__DIR__ . '/../app/controllers/' . $class . '.php')) {
        require_once __DIR__ . '/../app/controllers/' . $class . '.php';
    }
    // Jika ada kelas lain, sesuaikan dengan struktur folder Anda
});

// Buat instance Router dan arahkan ke URL saat ini
$router = new Router;

// Jika menggunakan mod_rewrite, URL yang diakses berada di parameter GET "route"
// Jika tidak, gunakan $_SERVER['REQUEST_URI']
$route = isset($_GET['route']) ? $_GET['route'] : $_SERVER['REQUEST_URI'];
$router->direct($route);
