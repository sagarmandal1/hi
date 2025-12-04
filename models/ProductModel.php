<?php
/**
 * Product Model
 * Customer & Real-Time Trading Management System
 */

require_once __DIR__ . '/../config/database.php';

class ProductModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Get all products
     * @return array
     */
    public function getAll() {
        $stmt = $this->db->query("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.deleted_at IS NULL 
            ORDER BY p.name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Find product by ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.id = ? AND p.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new product
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO products (name, category_id, notes) 
            VALUES (?, ?, ?)
        ");
        $result = $stmt->execute([
            $data['name'],
            $data['category_id'] ?: null,
            $data['notes'] ?? null
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Update product
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE products SET 
                name = ?, 
                category_id = ?, 
                notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['category_id'] ?: null,
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Soft delete product
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE products SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all categories
     * @return array
     */
    public function getCategories() {
        $stmt = $this->db->query("SELECT * FROM categories WHERE deleted_at IS NULL ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Create category
     * @param array $data
     * @return int|false
     */
    public function createCategory($data) {
        $stmt = $this->db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $result = $stmt->execute([
            $data['name'],
            $data['description'] ?? null
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Search products by name
     * @param string $search
     * @return array
     */
    public function search($search) {
        $stmt = $this->db->prepare("
            SELECT id, name FROM products 
            WHERE name LIKE ? AND deleted_at IS NULL 
            ORDER BY name ASC 
            LIMIT 20
        ");
        $stmt->execute(['%' . $search . '%']);
        return $stmt->fetchAll();
    }
}
