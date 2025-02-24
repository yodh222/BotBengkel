<?php
// app/models/Pelanggan.php

require_once __DIR__ . '/../../core/Model.php';

class Pelanggan extends Model
{
    // Nama tabel yang digunakan
    protected $table = 'tbpelanggan';
    // Kolom yang tidak boleh diisi secara massal (untuk referensi)
    protected $guarded = ['id'];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mengambil data pelanggan berdasarkan KodePelanggan.
     *
     * @param string $kode Kode pelanggan yang dicari.
     * @return array|false Data pelanggan dalam bentuk array asosiatif, atau false jika tidak ditemukan.
     */
    public static function findByKode($kode)
    {
        $instance = new self();
        $sql = "SELECT * FROM " . $instance->table . " WHERE KodePelanggan = :kode LIMIT 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->bindParam(':kode', $kode);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function findByKodeIn(array $kodeArray)
    {
        $instance = new self();
        if (empty($kodeArray)) {
            return [];
        }
        // Buat placeholder untuk prepared statement
        $placeholders = implode(',', array_fill(0, count($kodeArray), '?'));
        $sql = "SELECT * FROM " . $instance->table . " WHERE KodePelanggan IN ($placeholders)";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute($kodeArray);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Mengelompokkan data berdasarkan KodePelanggan
        $data = [];
        foreach ($results as $row) {
            $data[$row['KodePelanggan']] = $row;
        }
        return $data;
    }
}
