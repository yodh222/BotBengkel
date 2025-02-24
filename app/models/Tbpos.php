<?php
// app/models/Tbpos.php

require_once __DIR__ . '/../../core/Model.php';

class Tbpos extends Model
{
    protected $table = "tbpos";
    protected $guarded = ['id'];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mengambil data dari tabel tbpos berdasarkan NoFaktur.
     *
     * @param string $nofaktur
     * @return array|false Data tbpos dalam bentuk array asosiatif atau false jika tidak ditemukan.
     */
    public static function findByNoFaktur($nofaktur)
    {
        $instance = new self();
        $sql = "SELECT * FROM " . $instance->table . " WHERE NoFaktur = :nofaktur LIMIT 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->bindParam(':nofaktur', $nofaktur);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil transaksi terbaru per pelanggan menggunakan window function.
     * Pastikan MySQL yang digunakan mendukung window function.
     *
     * @return array of objects
     */
    public static function getNewestTransactions()
    {
        $instance = new self();
        $sql = "SELECT * FROM (
                    SELECT tp.NoFaktur, tp.TglFaktur, tp.NoPolisi, tp.KodePelanggan,
                           ROW_NUMBER() OVER (PARTITION BY tp.KodePelanggan ORDER BY tp.TglFaktur DESC) AS rn
                    FROM " . $instance->table . " tp
                ) AS Ranked
                WHERE rn = 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getNewestTransactionsWithNotifications($days = 30)
    {
        $instance = new self();

        $sql = "
        SELECT 
            NoFaktur, 
            TglFaktur, 
            NoPolisi, 
            KodePelanggan
        FROM " . $instance->table . "
        WHERE DATE(TglFaktur) = DATE_SUB(CURDATE(), INTERVAL :days DAY)
    ";

        $stmt = $instance->db->prepare($sql);
        $stmt->bindParam(':days', $days, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }
}
