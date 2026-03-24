<?php

require_once __DIR__ . '/BaseModel.php';

class Booking extends BaseModel {
    public function __construct() {
        parent::__construct('bookings');
    }

    public function getAllWithDetails() {
        $query = "
            SELECT b.*, u.username as guest_name, r.room_number, rt.name as type_name
            FROM {$this->table} b
            JOIN users u ON b.guest_id = u.id
            JOIN rooms r ON b.room_id = r.id
            JOIN room_types rt ON r.room_type_id = rt.id
            ORDER BY b.check_in DESC
        ";
        $stmt = $this->db->prepare($query);
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAllWithDetailsPaginated($limit = 10, $offset = 0) {
        $query = "
            SELECT b.*, u.username as guest_name, r.room_number, rt.name as type_name
            FROM {$this->table} b
            JOIN users u ON b.guest_id = u.id
            JOIN rooms r ON b.room_id = r.id
            JOIN room_types rt ON r.room_type_id = rt.id
            ORDER BY b.check_in DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getBookingsByUserIdPaginated($user_id, $limit = 10, $offset = 0) {
        $query = "
            SELECT b.*, r.room_number, rt.name as type_name, rev.rating 
            FROM {$this->table} b
            JOIN rooms r ON b.room_id = r.id
            JOIN room_types rt ON r.room_type_id = rt.id
            LEFT JOIN reviews rev ON b.id = rev.booking_id
            WHERE b.guest_id = :uid
            ORDER BY b.id DESC
            LIMIT :limit OFFSET :offset
        ";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':uid', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countBookingsByUserId($user_id) {
        $query = "SELECT COUNT(id) FROM {$this->table} WHERE guest_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        return $stmt->fetchColumn();
    }

    public function create($data) {
        $query = "INSERT INTO {$this->table} 
            (guest_id, room_id, check_in, check_out, guests_count, total_price, status, special_request) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            $data['guest_id'],
            $data['room_id'],
            $data['check_in'],
            $data['check_out'],
            $data['guests_count'],
            $data['total_price'],
            $data['status'] ?? 'pending',
            $data['special_request'] ?? ''
        ]);
    }

    public function updateStatus($id, $status) {
        $query = "UPDATE {$this->table} SET status = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$status, $id]);
    }

    public function isRoomAvailable($room_id, $check_in, $check_out) {
        $query = "
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE room_id = ? 
            AND status != 'cancelled'
            AND (
                (check_in <= ? AND check_out >= ?) OR 
                (check_in <= ? AND check_out >= ?) OR
                (check_in >= ? AND check_out <= ?)
            )
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $room_id, 
            $check_in, $check_in, 
            $check_out, $check_out, 
            $check_in, $check_out 
        ]);
        
        $result = $stmt->fetch();
        return $result['count'] == 0;
    }
}
