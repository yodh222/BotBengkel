<?php
// core/Model.php

class Model
{
    protected $db;

    public function __construct()
    {
        // Menggunakan konfigurasi dari config.php untuk membuat koneksi database
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';
        try {
            $this->db = new PDO($dsn, DB_USER, DB_PASS);
            // Set error mode agar exception dilempar ketika terjadi kesalahan
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Koneksi database gagal: ' . $e->getMessage());
        }
    }
}
