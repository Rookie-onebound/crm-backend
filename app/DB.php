<?php
class DB
{
    private static ?PDO $instance = null;

    public static function conn(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';
            $dbPath = $config['database'];
            $dbDir = dirname($dbPath);
            if (!file_exists($dbDir)) {
                mkdir($dbDir, 0755, true);
            }
            if (!file_exists($dbPath)) {
                self::initDatabase($dbPath);
            }
            $dsn = 'sqlite:' . $dbPath;
            self::$instance = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }

    private static function initDatabase(string $dbPath): void
    {
        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $pdo->exec('CREATE TABLE IF NOT EXISTS customers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name VARCHAR(120) NOT NULL,
            company_name VARCHAR(200) NOT NULL DEFAULT "",
            phone VARCHAR(40) NOT NULL DEFAULT "",
            email VARCHAR(200) NOT NULL DEFAULT "",
            source VARCHAR(100) NOT NULL DEFAULT "",
            register_ip VARCHAR(45) NOT NULL DEFAULT "",
            level TEXT NOT NULL DEFAULT "bronze",
            tags TEXT NULL,
            consume_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
            intention_level TEXT NOT NULL DEFAULT "medium",
            last_contact_time DATE NULL,
            notes TEXT NULL,
            industry VARCHAR(100) NOT NULL DEFAULT "",
            assigned_to VARCHAR(60) NOT NULL DEFAULT "",
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_customers_level ON customers(level)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_customers_source ON customers(source)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_customers_created_at ON customers(created_at)');

        $pdo->exec('CREATE TABLE IF NOT EXISTS customer_quotes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_id INTEGER NOT NULL,
            quote_title VARCHAR(200) NOT NULL,
            quote_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
            quote_status TEXT NOT NULL DEFAULT "draft",
            quote_content TEXT NULL,
            currency VARCHAR(10) NOT NULL DEFAULT "CNY",
            valid_until DATE NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
        )');

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_quotes_customer_id ON customer_quotes(customer_id)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_quotes_status ON customer_quotes(quote_status)');

        $pdo->exec('CREATE TABLE IF NOT EXISTS api_products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            platform VARCHAR(100) NOT NULL,
            api_name VARCHAR(200) NOT NULL,
            api_path VARCHAR(300) NOT NULL DEFAULT "",
            price DECIMAL(12,4) NOT NULL DEFAULT 0.0000,
            status TEXT NOT NULL DEFAULT "new",
            old_price DECIMAL(12,4) NULL,
            currency VARCHAR(10) NOT NULL DEFAULT "CNY",
            billing_unit VARCHAR(60) NOT NULL DEFAULT "",
            change_percent DECIMAL(6,2) NULL,
            effective_date DATE NULL,
            description TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )');

        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_products_platform ON api_products(platform)');
        $pdo->exec('CREATE INDEX IF NOT EXISTS idx_products_status ON api_products(status)');

        $pdo->exec("INSERT INTO customers (customer_name, company_name, phone, email, source, register_ip, level, tags, consume_amount, intention_level, last_contact_time, notes, industry, assigned_to) VALUES
            ('陈伟', '星辰科技有限公司', '138-0001-0001', 'chenwei@xingchen.com', '展会', '58.213.47.120', 'vip', '[\"重点客户\",\"长期合作\",\"高意向\"]', 2850000.00, 'high', '2026-06-04', '公司年营收约5000万', '互联网/电商', '张明'),
            ('李娜', '蓝鲸数据集团', '139-0002-0002', 'lina@bluewhale.com', '转介绍', '114.242.88.55', 'vip', '[\"战略合作\",\"VIP客户\"]', 3200000.00, 'high', '2026-06-05', '已签署战略合作协议', '大数据/AI', '李伟'),
            ('王强', '极光互娱网络科技', '137-0003-0003', 'wangqiang@aurora.com', '线上广告', '36.112.3.180', 'gold', '[\"游戏行业\",\"高并发\"]', 1250000.00, 'high', '2026-05-28', '游戏高峰期并发QPS需求达50万', '游戏/娱乐', '王芳'),
            ('赵敏', '云端教育科技', '136-0004-0004', 'zhaomin@cloudedu.cn', '合作伙伴推荐', '101.88.149.33', 'gold', '[\"教育\",\"视频直播\"]', 980000.00, 'medium', '2026-05-25', '主要使用视频相关API', '在线教育', '张明'),
            ('刘洋', '锋行物流供应链', '135-0005-0005', 'liuyang@fenghang.com', '官网注册', '121.35.208.76', 'silver', '[\"物流\",\"地图API\"]', 560000.00, 'medium', '2026-06-01', '重度依赖地图和路径规划API', '物流/供应链', '陈晨')");

        $pdo->exec("INSERT INTO customer_quotes (customer_id, quote_title, quote_amount, quote_status, quote_content, currency, valid_until) VALUES
            (1, 'API性能优化方案报价', 180000.00, 'sent', '包含3个月技术支持和7x24监控', 'CNY', '2026-06-30'),
            (1, 'Q3新增API接口开发', 250000.00, 'draft', '包括实时数据推送和批量处理接口', 'CNY', '2026-07-15'),
            (2, '战略合作协议续签', 5500000.00, 'negotiating', '包含SLA 99.99%保障和专属客服经理', 'CNY', '2026-07-15'),
            (3, '高并发解决方案', 350000.00, 'sent', '支持峰值QPS 50万+，P99延迟<200ms', 'CNY', '2026-06-25')");

        $pdo->exec("INSERT INTO api_products (platform, api_name, api_path, price, status, old_price, currency, billing_unit, change_percent, effective_date, description) VALUES
            ('微信支付', '统一下单', '/v3/pay/transactions/app', 0.0055, 'changed', 0.006, 'CNY', '每笔交易', -8.30, '2026-06-15', '微信支付下调商户费率'),
            ('百度AI', '文字识别OCR', '/rest/2.0/ocr/v1/general', 0.0035, 'changed', 0.004, 'CNY', '每次调用', -12.50, '2026-06-10', '通用文字识别价格下调'),
            ('抖音开放平台', '视频智能剪辑', '/video/intelligent/edit', 0.05, 'new', NULL, 'CNY', '每分钟视频', NULL, '2026-06-20', '新增视频智能剪辑API'),
            ('百度AI', '图像审核', '/rest/2.0/solution/v1/img_censor', 0.003, 'changed', 0.0025, 'CNY', '每次调用', 20.00, '2026-07-01', '图像审核升级多模态模型'),
            ('高德地图', '旧版地图SDK', '/v3/place/text', 0, 'deprecated', 0.01, 'CNY', '每次调用', -100.00, '2026-08-01', '旧版SDK即将下线'),
            ('百度AI', '语音合成TTS', '/rest/2.0/tts/v1/synthesize', 0.008, 'new', NULL, 'CNY', '每千字符', NULL, '2026-06-18', '新增高品质语音合成API'),
            ('Stripe', '订阅管理', '/v1/subscriptions', 0.022, 'changed', 0.025, 'USD', '每笔订阅', -12.00, '2026-06-08', '订阅管理费率调整'),
            ('Stripe', '跨境支付', '/v1/payment_intents', 0.035, 'new', NULL, 'USD', '每笔交易', NULL, '2026-06-25', '新增亚太区跨境支付API'),
            ('腾讯云短信', '短信发送（营销）', '/v5/sms/marketing/send', 0.05, 'changed', 0.045, 'CNY', '每条', 11.10, '2026-07-01', '运营商资费调整')");
    }
}
