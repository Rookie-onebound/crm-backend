<?php
/**
 * 客户 Model
 */
class Customer
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::conn();
    }

    /** 获取客户列表（支持搜索和筛选） */
    public function getAll(array $filters = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filters['search'])) {
            $where[] = '(customer_name LIKE ? OR company_name LIKE ? OR phone LIKE ?)';
            $q = '%' . $filters['search'] . '%';
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }
        if (!empty($filters['level'])) {
            $where[] = '`level` = ?';
            $params[] = $filters['level'];
        }

        $sql = 'SELECT * FROM customers';
        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY created_at DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // 将 JSON tags 解码为数组
        return array_map(function ($row) {
            $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
            $row['id'] = (string) $row['id'];
            return $row;
        }, $rows);
    }

    /** 获取单个客户 */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM customers WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;

        $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
        $row['id'] = (string) $row['id'];
        return $row;
    }

    /** 新增客户 */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO customers (customer_name, company_name, phone, email, source, register_ip, `level`, tags, consume_amount, intention_level, last_contact_time, notes, industry, assigned_to)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['customer_name']     ?? '',
            $data['company_name']      ?? '',
            $data['phone']             ?? '',
            $data['email']             ?? '',
            $data['source']            ?? '',
            $data['register_ip']       ?? '',
            $data['level']            ?? 'bronze',
            json_encode($data['tags'] ?? [], JSON_UNESCAPED_UNICODE),
            $data['consume_amount']   ?? 0,
            $data['intention_level']  ?? 'medium',
            $data['last_contact_time'] ?? date('Y-m-d'),
            $data['notes']            ?? '',
            $data['industry']         ?? '',
            $data['assigned_to']      ?? '',
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** 更新客户 */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        $allowed = ['customer_name','company_name','phone','email','source','register_ip','level','consume_amount','intention_level','last_contact_time','notes','industry','assigned_to'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "`$field` = ?";
                $params[] = $data[$field];
            }
        }
        if (array_key_exists('tags', $data)) {
            $fields[] = '`tags` = ?';
            $params[] = json_encode($data['tags'], JSON_UNESCAPED_UNICODE);
        }

        if (empty($fields)) return false;

        $params[] = $id;
        $stmt = $this->db->prepare('UPDATE customers SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($params);
    }

    /** 删除客户 */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM customers WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /** 获取所有客户（用于导出） */
    public function getAllForExport(): array
    {
        $stmt = $this->db->query('SELECT * FROM customers ORDER BY created_at DESC');
        $rows = $stmt->fetchAll();
        return array_map(function ($row) {
            $row['tags'] = json_decode($row['tags'] ?? '[]', true) ?: [];
            return $row;
        }, $rows);
    }
}
