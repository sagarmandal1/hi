<?php
/**
 * User Model
 * Customer & Real-Time Trading Management System
 */

require_once __DIR__ . '/../config/database.php';

class UserModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Find user by email
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? AND deleted_at IS NULL AND is_active = 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Find user by ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Verify password
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Update password
     * @param int $userId
     * @param string $newPassword
     * @return bool
     */
    public function updatePassword($userId, $newPassword) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        return $stmt->execute([$hash, $userId]);
    }

    /**
     * Create new user
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO users (name, email, password, role) 
            VALUES (?, ?, ?, ?)
        ");
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $result = $stmt->execute([
            $data['name'],
            $data['email'],
            $hash,
            $data['role'] ?? 'staff'
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Get all users
     * @return array
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT id, name, email, role, is_active, created_at FROM users WHERE deleted_at IS NULL ORDER BY name");
        return $stmt->fetchAll();
    }
}
