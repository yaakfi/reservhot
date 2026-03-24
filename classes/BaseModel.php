<?php

require_once __DIR__ . '/../config/Database.php';

abstract class BaseModel {
    protected $db;
    protected $table;

    public function __construct($tableName) {
        $this->db = Database::getInstance()->getConnection();
        $this->table = $tableName;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllPaginated($limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY id DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function countAll() {
        $stmt = $this->db->query("SELECT COUNT(id) FROM {$this->table}");
        return $stmt->fetchColumn();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
