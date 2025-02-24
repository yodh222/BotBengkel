<?php
// app/models/BotToken.php

require_once __DIR__ . '/../../core/Model.php';

class BotToken extends Model
{
    protected $table = 'tb_bot_token';
    protected $guarded = ['id'];

    public function __construct()
    {
        parent::__construct();
    }

    public static function getToken()
    {
        $instance = new self();
        $sql = "SELECT token FROM " . $instance->table . " LIMIT 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['token'] : '';
    }

    public static function updateToken($newToken)
    {
        $instance = new self();
        $sql = "UPDATE " . $instance->table . " SET token = :token WHERE id = 1";
        $stmt = $instance->db->prepare($sql);
        $stmt->bindValue(':token', $newToken);
        return $stmt->execute();
    }
}
