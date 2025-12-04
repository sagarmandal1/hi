<?php
/**
 * Expense Model
 * Customer & Real-Time Trading Management System
 */

require_once __DIR__ . '/../config/database.php';

class ExpenseModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    /**
     * Get all expenses
     * @param array $filters
     * @return array
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT e.*, ec.name as category_name
            FROM expenses e 
            LEFT JOIN expense_categories ec ON e.category_id = ec.id 
            WHERE e.deleted_at IS NULL
        ";
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND e.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND e.expense_date >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND e.expense_date <= ?";
            $params[] = $filters['end_date'];
        }

        $sql .= " ORDER BY e.expense_date DESC, e.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Find expense by ID
     * @param int $id
     * @return array|false
     */
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT e.*, ec.name as category_name
            FROM expenses e 
            LEFT JOIN expense_categories ec ON e.category_id = ec.id 
            WHERE e.id = ? AND e.deleted_at IS NULL
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create new expense
     * @param array $data
     * @return int|false
     */
    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO expenses (category_id, amount, expense_date, notes) 
            VALUES (?, ?, ?, ?)
        ");
        $result = $stmt->execute([
            $data['category_id'] ?: null,
            $data['amount'],
            $data['expense_date'],
            $data['notes'] ?? null
        ]);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Update expense
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE expenses SET 
                category_id = ?, 
                amount = ?, 
                expense_date = ?, 
                notes = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['category_id'] ?: null,
            $data['amount'],
            $data['expense_date'],
            $data['notes'] ?? null,
            $id
        ]);
    }

    /**
     * Soft delete expense
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE expenses SET deleted_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get all expense categories
     * @return array
     */
    public function getCategories() {
        $stmt = $this->db->query("SELECT * FROM expense_categories WHERE deleted_at IS NULL ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    /**
     * Create expense category
     * @param string $name
     * @return int|false
     */
    public function createCategory($name) {
        $stmt = $this->db->prepare("INSERT INTO expense_categories (name) VALUES (?)");
        $result = $stmt->execute([$name]);
        return $result ? $this->db->lastInsertId() : false;
    }

    /**
     * Get day total expenses
     * @param string $date
     * @return float
     */
    public function getDayTotal($date) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE expense_date = ? AND deleted_at IS NULL");
        $stmt->execute([$date]);
        return $stmt->fetch()['total'];
    }

    /**
     * Get date range total expenses
     * @param string $startDate
     * @param string $endDate
     * @return float
     */
    public function getDateRangeTotal($startDate, $endDate) {
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE expense_date BETWEEN ? AND ? AND deleted_at IS NULL");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetch()['total'];
    }
}
