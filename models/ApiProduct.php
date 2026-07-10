<?php
/**
 * API 产品 Model
 */
class ApiProduct
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::conn();
    }

    /** 获取所有价格变动记录 */
    public function getAll(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = '`status` = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['platform'])) {
            $where[] = '`platform` = ?';
            $params[] = $filters['platform'];
        }

        $sql = 'SELECT * FROM api_products';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY effective_date DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return array_map(function ($row) {
            $row['id'] = (string) $row['id'];
            return $row;
        }, $stmt->fetchAll());
    }

    /** 获取所有（导出用） */
    public function getAllForExport(): array
    {
        $stmt = $this->db->query('SELECT * FROM api_products ORDER BY effective_date DESC');
        return array_map(function ($row) {
            $row['id'] = (string) $row['id'];
            return $row;
        }, $stmt->fetchAll());
    }
}
