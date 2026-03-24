<?php
require_once __DIR__ . '/BaseModel.php';

class RoomType extends BaseModel {
    public function __construct() {
        parent::__construct('room_types');
    }

    public function createType($name, $description, $base_price, $max_occupancy, $amenities, $image) {
        $query = "INSERT INTO {$this->table} (name, description, base_price, max_occupancy, amenities, image) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$name, $description, $base_price, $max_occupancy, $amenities, $image]);
    }

    public function updateType($id, $name, $description, $base_price, $max_occupancy, $amenities, $image) {
        if (!empty($image)) {
            $query = "UPDATE {$this->table} SET name = ?, description = ?, base_price = ?, max_occupancy = ?, amenities = ?, image = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$name, $description, $base_price, $max_occupancy, $amenities, $image, $id]);
        } else {
            $query = "UPDATE {$this->table} SET name = ?, description = ?, base_price = ?, max_occupancy = ?, amenities = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$name, $description, $base_price, $max_occupancy, $amenities, $id]);
        }
    }
}
?>
