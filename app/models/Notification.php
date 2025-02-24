<?php
// app/models/Notification.php

require_once __DIR__ . '/../../core/Model.php';

class Notification extends Model
{
    // Nama tabel yang digunakan
    protected $table = "tb_notifications";
    // Data yang tidak boleh diisi secara massal (hanya untuk referensi)
    protected $guarded = ['id'];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mengambil seluruh data notifikasi dari tabel.
     *
     * @return array Data notifikasi dalam bentuk array asosiatif.
     */
    public static function all()
    {
        $instance = new self();
        $sql = "SELECT * FROM " . $instance->table;
        $stmt = $instance->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mengambil satu notifikasi berdasarkan ID.
     *
     * @param int $id
     * @return array|false Data notifikasi dalam bentuk array asosiatif atau false jika tidak ditemukan.
     */
    public static function find($id)
    {
        $instance = new self();
        $sql = "SELECT * FROM " . $instance->table . " WHERE id = :id LIMIT 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Menyimpan data notifikasi baru ke dalam tabel.
     *
     * @param array $data Data yang akan disimpan, misalnya ['judul' => '...', 'pesan' => '...', 'interval_days' => 30, 'status' => 'active']
     * @return int|false ID dari record yang baru dibuat atau false jika gagal.
     */
    public static function store($data)
    {
        $instance = new self();
        $now = date("Y-m-d H:i:s");
        if (!isset($data['created_at'])) {
            $data['created_at'] = $now;
        }
        if (!isset($data['updated_at'])) {
            $data['updated_at'] = $now;
        }
        $fields = array_keys($data);
        $placeholders = array_map(function ($field) {
            return ':' . $field;
        }, $fields);

        $sql = "INSERT INTO " . $instance->table . " (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $instance->db->prepare($sql);

        foreach ($data as $field => $value) {
            $stmt->bindValue(':' . $field, $value);
        }

        if ($stmt->execute()) {
            return $instance->db->lastInsertId();
        } else {
            // Debugging: cetak error info
            $error = $stmt->errorInfo();
            error_log("SQL Error in Notification::store(): " . print_r($error, true));
            return false;
        }
    }


    /**
     * Mengupdate data notifikasi berdasarkan ID.
     *
     * @param int $id
     * @param array $data Data yang akan diupdate, misalnya ['judul' => '...', 'pesan' => '...']
     * @return bool True jika update berhasil, false jika gagal.
     */
    public static function updateRecord($id, $data)
    {
        $instance = new self();

        // Update updated_at setiap kali ada perubahan
        $data['updated_at'] = date("Y-m-d H:i:s");

        $fields = [];
        foreach ($data as $field => $value) {
            $fields[] = "$field = :$field";
        }
        $sql = "UPDATE " . $instance->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $instance->db->prepare($sql);

        foreach ($data as $field => $value) {
            $stmt->bindValue(':' . $field, $value);
        }
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Menghapus notifikasi berdasarkan ID.
     *
     * @param int $id
     * @return bool True jika penghapusan berhasil, false jika gagal.
     */
    public static function deleteRecord($id)
    {
        $instance = new self();
        $sql = "DELETE FROM " . $instance->table . " WHERE id = :id";
        $stmt = $instance->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
