<?php
/**
 * Customer Model
 * Customer & Real-Time Trading Management System
 */

require_once __DIR__ . '/../config/database.php';

class CustomerModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Get all customers
     * @param array $filters
     * @return array
     */
    public function getAll($filters = []) {
        $sql = "SELECT * FROM customers WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (name LIKE ? OR phone LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (isset($filters['is_active'])) {
            $sql .= " AND is_active = ?";
            $params[] = $filters['is_active'];
        }

        $sql .= " ORDER BY name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find customer by ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new customer
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO customers (name, phone, email, address, notes, is_active) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['address'] ?? null,
            $data['notes'] ?? null,
            $data['is_active'] ?? 1
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Update customer
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE customers SET 
                name = ?, 
                phone = ?, 
                email = ?, 
                address = ?, 
                notes = ?, 
                is_active = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['name'],
            $data['phone'],
            $data['email'] ?? null,
            $data['address'] ?? null,
            $data['notes'] ?? null,
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    /**
     * Soft delete customer
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE customers SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get customer statistics
     * @param int $customerId
     * @return array
     */
    public function getStats($customerId) {
        // Total deals
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_deals,
                COALESCE(SUM(total_sell_amount), 0) as total_sell,
                COALESCE(SUM(profit), 0) as total_profit
            FROM deals 
            WHERE customer_id = ? AND deleted_at IS NULL AND status != 'cancelled'
        ");
        $stmt->execute([$customerId]);
        $dealStats = $stmt->fetch();

        // Total paid
        $stmt = $this->db->prepare("
            SELECT COALESCE(SUM(amount), 0) as total_paid 
            FROM payments 
            WHERE customer_id = ? AND deleted_at IS NULL
        ");
        $stmt->execute([$customerId]);
        $paymentStats = $stmt->fetch();

        return [
            'total_deals' => $dealStats['total_deals'],
            'total_sell' => $dealStats['total_sell'],
            'total_profit' => $dealStats['total_profit'],
            'total_paid' => $paymentStats['total_paid'],
            'total_due' => $dealStats['total_sell'] - $paymentStats['total_paid']
        ];
    }

    /**
     * Get customers with dues
     * @return array
     */
    public function getWithDues() {
        $stmt = $this->db->query("
            SELECT 
                c.*,
                COALESCE(SUM(d.total_sell_amount), 0) as total_sell,
                COALESCE((
                    SELECT SUM(p.amount) 
                    FROM payments p 
                    WHERE p.customer_id = c.id AND p.deleted_at IS NULL
                ), 0) as total_paid
            FROM customers c
            LEFT JOIN deals d ON d.customer_id = c.id AND d.deleted_at IS NULL AND d.status != 'cancelled'
            WHERE c.deleted_at IS NULL
            GROUP BY c.id
            HAVING total_sell > total_paid
            ORDER BY (total_sell - total_paid) DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get total customer count
     * @return int
     */
    public function getTotalCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM customers WHERE deleted_at IS NULL");
        return $stmt->fetch()['count'];
    }
}
