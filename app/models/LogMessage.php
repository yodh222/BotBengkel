<?php
// app/models/LogMessage.php

require_once __DIR__ . '/../../core/Model.php';

class LogMessage extends Model
{
    protected $table = "tb_log_messages";
    protected $guarded = ['id'];

    public function __construct()
    {
        parent::__construct();
    }

    public static function all()
    {
        $instance = new self();
        $sql = "SELECT * FROM " . $instance->table;
        $stmt = $instance->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id)
    {
        $instance = new self();
        $sql = "SELECT * FROM " . $instance->table . " WHERE id = :id LIMIT 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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
        }
        return false;
    }

    public static function updateRecord($id, $data)
    {
        $instance = new self();
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

    public static function deleteRecord($id)
    {
        $instance = new self();
        $sql = "DELETE FROM " . $instance->table . " WHERE id = :id";
        $stmt = $instance->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Mengambil pesan-pesan berdasarkan NoFaktur.
     *
     * @param string $nofaktur
     * @return array
     */
    public static function getMessagesByNoFaktur($nofaktur)
    {
        $instance = new self();
        $sql = "SELECT pesan FROM " . $instance->table . " WHERE NoFaktur = ?";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute([$nofaktur]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getMessagesByNoFakturIn(array $noFakturArray)
    {
        $instance = new self();
        if (empty($noFakturArray)) {
            return [];
        }
        // Buat placeholder untuk prepared statement
        $placeholders = implode(',', array_fill(0, count($noFakturArray), '?'));
        $sql = "SELECT NoFaktur, pesan FROM " . $instance->table . " WHERE NoFaktur IN ($placeholders)";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute($noFakturArray);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Kelompokkan pesan berdasarkan NoFaktur
        $data = [];
        foreach ($results as $row) {
            $noFaktur = $row['NoFaktur'];
            if (!isset($data[$noFaktur])) {
                $data[$noFaktur] = [];
            }
            $data[$noFaktur][] = $row['pesan'];
        }
        return $data;
    }
}