<?php

require_once __DIR__ . '/BaseModel.php';

class Room extends BaseModel
{
    public function __construct()
    {
        parent::__construct('rooms'); // Set tabel target `rooms`
    }

    public function getAllWithTypes()
    {
        $query = "
            SELECT 
                r.*, rt.name as type_name, rt.base_price, rt.max_occupancy, rt.amenities, rt.image,
                IFNULL(ROUND(AVG(rev.rating), 1), 0) as avg_rating
            FROM {$this->table} r
            JOIN room_types rt ON r.room_type_id = rt.id
            LEFT JOIN bookings b ON r.id = b.room_id
            LEFT JOIN reviews rev ON b.id = rev.booking_id
            GROUP BY r.id, rt.name, rt.base_price, rt.max_occupancy, rt.amenities, rt.image
            ORDER BY r.floor, r.room_number ASC
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getAllWithTypesPaginated($limit = 10, $offset = 0) {
        $query = "
            SELECT 
                r.*, rt.name as type_name, rt.base_price, rt.max_occupancy, rt.amenities, rt.image,
                IFNULL(ROUND(AVG(rev.rating), 1), 0) as avg_rating
            FROM {$this->table} r
            JOIN room_types rt ON r.room_type_id = rt.id
            LEFT JOIN bookings b ON r.id = b.room_id
            LEFT JOIN reviews rev ON b.id = rev.booking_id
            GROUP BY r.id, rt.name, rt.base_price, rt.max_occupancy, rt.amenities, rt.image
            ORDER BY r.floor, r.room_number ASC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function create($data)
    {
        $query = "INSERT INTO {$this->table} (room_type_id, room_number, floor, status, notes) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['room_type_id'],
            $data['room_number'],
            $data['floor'],
            $data['status'],
            $data['notes']
        ]);
    }

    public function update($id, $data)
    {
        $query = "UPDATE {$this->table} SET room_type_id = ?, room_number = ?, floor = ?, status = ?, notes = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['room_type_id'],
            $data['room_number'],
            $data['floor'],
            $data['status'],
            $data['notes'],
            $id
        ]);
    }
}
