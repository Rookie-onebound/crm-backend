<?php
/**
 * 报价记录 Model
 */
class Quote
{
    private PDO $db;

    public function __construct()
    {
        $this->db = DB::conn();
    }

    /** 获取某个客户的所有报价 */
    public function getByCustomer(int $customerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM customer_quotes WHERE customer_id = ? ORDER BY created_at DESC'
        );
        $stmt->execute([$customerId]);
        return array_map(function ($row) {
            $row['id'] = (string) $row['id'];
            $row['customer_id'] = (string) $row['customer_id'];
            return $row;
        }, $stmt->fetchAll());
    }

    /** 获取单个报价 */
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM customer_quotes WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) return null;
        $row['id'] = (string) $row['id'];
        $row['customer_id'] = (string) $row['customer_id'];
        return $row;
    }

    /** 新增报价 */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO customer_quotes (customer_id, quote_title, quote_amount, quote_status, quote_content, currency, valid_until)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['customer_id']   ?? 0,
            $data['quote_title']   ?? '',
            $data['quote_amount']  ?? 0,
            $data['quote_status']  ?? 'draft',
            $data['quote_content'] ?? '',
            $data['currency']      ?? 'CNY',
            $data['valid_until']   ?? null,
        ]);
        return (int) $this->db->lastInsertId();
    }

    /** 更新报价 */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        $allowed = ['quote_title','quote_amount','quote_status','quote_content','currency','valid_until'];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "`$field` = ?";
                $params[] = $data[$field];
            }
        }
        if (empty($fields)) return false;
        $params[] = $id;
        $stmt = $this->db->prepare('UPDATE customer_quotes SET ' . implode(', ', $fields) . ' WHERE id = ?');
        return $stmt->execute($params);
    }

    /** 删除报价 */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM customer_quotes WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
