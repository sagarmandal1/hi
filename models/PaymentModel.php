<?php
/**
 * Payment Model
 * Customer & Real-Time Trading Management System
 */

require_once __DIR__ . '/../config/database.php';

class PaymentModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Get all payments
     * @param array $filters
     * @return array
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT p.*, d.deal_number, c.name as customer_name
            FROM payments p 
            LEFT JOIN deals d ON p.deal_id = d.id 
            LEFT JOIN customers c ON p.customer_id = c.id 
            WHERE p.deleted_at IS NULL
        ";
        $params = [];

        if (!empty($filters['customer_id'])) {
            $sql .= " AND p.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['deal_id'])) {
            $sql .= " AND p.deal_id = ?";
            $params[] = $filters['deal_id'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND p.payment_date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND p.payment_date <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY p.payment_date DESC, p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find payment by ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, d.deal_number, c.name as customer_name
            FROM payments p 
            LEFT JOIN deals d ON p.deal_id = d.id 
            LEFT JOIN customers c ON p.customer_id = c.id 
            WHERE p.id = ? AND p.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new payment
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO payments (deal_id, customer_id, amount, payment_method, payment_date, notes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $data['deal_id'],
            $data['customer_id'],
            $data['amount'],
            $data['payment_method'] ?? 'cash',
            $data['payment_date'],
            $data['notes'] ?? null
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Update payment
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE payments SET 
                deal_id = ?, 
                customer_id = ?, 
                amount = ?, 
                payment_method = ?, 
                payment_date = ?, 
                notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['deal_id'],
            $data['customer_id'],
            $data['amount'],
            $data['payment_method'],
            $data['payment_date'],
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Soft delete payment
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE payments SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get payments by deal
     * @param int $dealId
     * @return array
     */
    public function getByDeal($dealId) {
        $stmt = $this->db->prepare("
            SELECT * FROM payments 
            WHERE deal_id = ? AND deleted_at IS NULL 
            ORDER BY payment_date DESC
        ");
        $stmt->execute([$dealId]);
        return $stmt->fetchAll();
    }

    /**
     * Get payments by customer
     * @param int $customerId
     * @return array
     */
    public function getByCustomer($customerId) {
        $stmt = $this->db->prepare("
            SELECT p.*, d.deal_number 
            FROM payments p 
            LEFT JOIN deals d ON p.deal_id = d.id 
            WHERE p.customer_id = ? AND p.deleted_at IS NULL 
            ORDER BY p.payment_date DESC
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    /**
     * Get total paid for a deal
     * @param int $dealId
     * @return float
     */
    public function getTotalPaidForDeal($dealId) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE deal_id = ? AND deleted_at IS NULL");
        $stmt->execute([$dealId]);
        return $stmt->fetch()['total'];
    }

    /**
     * Get total paid for a customer
     * @param int $customerId
     * @return float
     */
    public function getTotalPaidByCustomer($customerId) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE customer_id = ? AND deleted_at IS NULL");
        $stmt->execute([$customerId]);
        return $stmt->fetch()['total'];
    }
}
