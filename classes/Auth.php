<?php

require_once __DIR__ . '/BaseModel.php';

class Auth extends BaseModel {
    private $session_prefix = 'hotel_res_';

    public function __construct() {
        parent::__construct('users');
    }

    public function register($data) {
        $query = "INSERT INTO {$this->table} (username, password, email, role, phone) VALUES (?, ?, ?, ?, ?)";
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $role = isset($data['role']) ? $data['role'] : 'guest';

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['username'],
            $hashed_password,
            $data['email'],
            $role,
            $data['phone']
        ]);
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION[$this->session_prefix . 'logged_in'] = true;
            $_SESSION[$this->session_prefix . 'user_id']   = $user['id'];
            $_SESSION[$this->session_prefix . 'username']  = $user['username'];
            $_SESSION[$this->session_prefix . 'role']      = $user['role'];
            return true;
        }
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
    }

    public function isLoggedIn() {
        return isset($_SESSION[$this->session_prefix . 'logged_in']) && $_SESSION[$this->session_prefix . 'logged_in'] === true;
    }

    public function hasRole($roles = []) {
        if (!$this->isLoggedIn()) return false;
        
        $user_role = $_SESSION[$this->session_prefix . 'role'];
        return in_array($user_role, (array)$roles);
    }
}
