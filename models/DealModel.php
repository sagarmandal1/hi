<?php
/**
 * Deal Model
 * Customer & Real-Time Trading Management System
 */

require_once __DIR__ . '/../config/database.php';

class DealModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Get all deals
     * @param array $filters
     * @return array
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT d.*, c.name as customer_name, c.phone as customer_phone
            FROM deals d 
            LEFT JOIN customers c ON d.customer_id = c.id 
            WHERE d.deleted_at IS NULL
        ";
        $params = [];

        if (!empty($filters['customer_id'])) {
            $sql .= " AND d.customer_id = ?";
            $params[] = $filters['customer_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND d.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND d.deal_date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND d.deal_date <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY d.deal_date DESC, d.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find deal by ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT d.*, c.name as customer_name, c.phone as customer_phone
            FROM deals d 
            LEFT JOIN customers c ON d.customer_id = c.id 
            WHERE d.id = ? AND d.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new deal
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO deals (deal_number, customer_id, deal_date, status, total_buy_amount, total_sell_amount, profit, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $data['deal_number'],
            $data['customer_id'],
            $data['deal_date'],
            $data['status'] ?? 'pending',
            $data['total_buy_amount'],
            $data['total_sell_amount'],
            $data['profit'],
            $data['notes'] ?? null
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Update deal
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE deals SET 
                customer_id = ?, 
                deal_date = ?, 
                status = ?, 
                total_buy_amount = ?, 
                total_sell_amount = ?, 
                profit = ?, 
                notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['customer_id'],
            $data['deal_date'],
            $data['status'],
            $data['total_buy_amount'],
            $data['total_sell_amount'],
            $data['profit'],
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Soft delete deal
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE deals SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Add deal item
     * @param array $data
     * @return int|false
     */
    public function addItem($data) {
        $stmt = $this->db->prepare("
            INSERT INTO deal_items (deal_id, product_id, product_name, buy_quantity, buy_price, total_buy, sell_quantity, sell_price, total_sell, profit) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $data['deal_id'],
            $data['product_id'] ?: null,
            $data['product_name'],
            $data['buy_quantity'],
            $data['buy_price'],
            $data['total_buy'],
            $data['sell_quantity'],
            $data['sell_price'],
            $data['total_sell'],
            $data['profit']
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Get deal items
     * @param int $dealId
     * @return array
     */
    public function getItems($dealId) {
        $stmt = $this->db->prepare("
            SELECT di.*, p.name as product_ref_name 
            FROM deal_items di 
            LEFT JOIN products p ON di.product_id = p.id 
            WHERE di.deal_id = ?
        ");
        $stmt->execute([$dealId]);
        return $stmt->fetchAll();
    }

    /**
     * Delete deal items
     * @param int $dealId
     * @return bool
     */
    public function deleteItems($dealId) {
        $stmt = $this->db->prepare("DELETE FROM deal_items WHERE deal_id = ?");
        return $stmt->execute([$dealId]);
    }

    /**
     * Get deals by customer
     * @param int $customerId
     * @return array
     */
    public function getByCustomer($customerId) {
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   COALESCE((SELECT SUM(p.amount) FROM payments p WHERE p.deal_id = d.id AND p.deleted_at IS NULL), 0) as total_paid
            FROM deals d 
            WHERE d.customer_id = ? AND d.deleted_at IS NULL 
            ORDER BY d.deal_date DESC
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    /**
     * Get deal statistics for dashboard
     * @param string $date
     * @return array
     */
    public function getDayStats($date) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_deals,
                COALESCE(SUM(total_buy_amount), 0) as total_buy,
                COALESCE(SUM(total_sell_amount), 0) as total_sell,
                COALESCE(SUM(profit), 0) as total_profit
            FROM deals 
            WHERE deal_date = ? AND deleted_at IS NULL AND status != 'cancelled'
        ");
        $stmt->execute([$date]);
        return $stmt->fetch();
    }

    /**
     * Get total deal count
     * @return int
     */
    public function getTotalCount() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM deals WHERE deleted_at IS NULL");
        return $stmt->fetch()['count'];
    }

    /**
     * Get deal statistics for date range
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getDateRangeStats($startDate, $endDate) {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_deals,
                COALESCE(SUM(total_buy_amount), 0) as total_buy,
                COALESCE(SUM(total_sell_amount), 0) as total_sell,
                COALESCE(SUM(profit), 0) as total_profit
            FROM deals 
            WHERE deal_date BETWEEN ? AND ? AND deleted_at IS NULL AND status != 'cancelled'
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetch();
    }

    /**
     * Get total due from all deals
     * @return float
     */
    public function getTotalDue() {
        $stmt = $this->db->query("
            SELECT 
                COALESCE(SUM(d.total_sell_amount), 0) - COALESCE((
                    SELECT SUM(p.amount) FROM payments p WHERE p.deleted_at IS NULL
                ), 0) as total_due
            FROM deals d 
            WHERE d.deleted_at IS NULL AND d.status != 'cancelled'
        ");
        $result = $stmt->fetch();
        return max(0, $result['total_due'] ?? 0);
    }

    /**
     * Generate next deal number
     * @return string
     */
    public function generateDealNumber() {
        $stmt = $this->db->query("SELECT MAX(id) as max_id FROM deals");
        $result = $stmt->fetch();
        $nextId = ($result['max_id'] ?? 0) + 1;
        return 'DEAL-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
}
