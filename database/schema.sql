-- ============================================
-- Nexus CRM 数据库初始化脚本
-- ============================================

CREATE DATABASE IF NOT EXISTS `nexus_crm`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `nexus_crm`;

-- ============================================
-- 1. 客户表 customers
-- ============================================
CREATE TABLE IF NOT EXISTS `customers` (
  `id`              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `customer_name`   VARCHAR(120)    NOT NULL COMMENT '客户名称',
  `company_name`    VARCHAR(200)    NOT NULL DEFAULT '' COMMENT '公司名称',
  `phone`           VARCHAR(40)     NOT NULL DEFAULT '' COMMENT '手机号',
  `email`           VARCHAR(200)    NOT NULL DEFAULT '' COMMENT '邮箱',
  `source`          VARCHAR(100)    NOT NULL DEFAULT '' COMMENT '客户来源',
  `register_ip`     VARCHAR(45)     NOT NULL DEFAULT '' COMMENT '注册IP',
  `level`           ENUM('vip','gold','silver','bronze','dormant','invalid')
                                    NOT NULL DEFAULT 'bronze' COMMENT '客户等级',
  `tags`            JSON            NULL     COMMENT '标签列表',
  `consume_amount`  DECIMAL(14,2)   NOT NULL DEFAULT 0.00 COMMENT '消费金额',
  `intention_level` ENUM('high','medium','low')
                                    NOT NULL DEFAULT 'medium' COMMENT '意向程度',
  `last_contact_time` DATE         NULL     COMMENT '最近联系时间',
  `notes`           TEXT            NULL     COMMENT '备注',
  `industry`        VARCHAR(100)    NOT NULL DEFAULT '' COMMENT '行业',
  `assigned_to`     VARCHAR(60)     NOT NULL DEFAULT '' COMMENT '负责人',
  `created_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at`      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_level` (`level`),
  INDEX `idx_source` (`source`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_updated_at` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户表';


-- ============================================
-- 2. 客户报价记录表 customer_quotes
-- ============================================
CREATE TABLE IF NOT EXISTS `customer_quotes` (
  `id`            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `customer_id`   INT UNSIGNED    NOT NULL COMMENT '关联客户ID',
  `quote_title`   VARCHAR(200)    NOT NULL COMMENT '报价名称',
  `quote_amount`  DECIMAL(14,2)   NOT NULL DEFAULT 0.00 COMMENT '报价金额',
  `quote_status`  ENUM('draft','sent','negotiating','accepted','rejected','expired')
                                  NOT NULL DEFAULT 'draft' COMMENT '报价状态',
  `quote_content` TEXT            NULL     COMMENT '报价备注/内容',
  `currency`      VARCHAR(10)     NOT NULL DEFAULT 'CNY' COMMENT '币种',
  `valid_until`   DATE            NULL     COMMENT '有效期',
  `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_status` (`quote_status`),
  CONSTRAINT `fk_quote_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户报价记录表';


-- ============================================
-- 3. API产品表 api_products
-- ============================================
CREATE TABLE IF NOT EXISTS `api_products` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `platform`    VARCHAR(100)    NOT NULL COMMENT '平台名称',
  `api_name`    VARCHAR(200)    NOT NULL COMMENT 'API名称',
  `api_path`    VARCHAR(300)    NOT NULL DEFAULT '' COMMENT 'API路径',
  `price`       DECIMAL(12,4)   NOT NULL DEFAULT 0.0000 COMMENT '单价',
  `status`      ENUM('changed','new','deprecated')
                                NOT NULL DEFAULT 'new' COMMENT '状态',
  `old_price`   DECIMAL(12,4)   NULL     COMMENT '原价',
  `currency`    VARCHAR(10)     NOT NULL DEFAULT 'CNY' COMMENT '币种',
  `billing_unit` VARCHAR(60)   NOT NULL DEFAULT '' COMMENT '计费单位',
  `change_percent` DECIMAL(6,2) NULL    COMMENT '变动百分比',
  `effective_date` DATE         NULL     COMMENT '生效日期',
  `description` TEXT            NULL     COMMENT '描述',
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  INDEX `idx_platform` (`platform`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='API产品表';


-- ============================================
-- 预置示例数据
-- ============================================

-- 客户示例数据
INSERT INTO `customers` (`customer_name`, `company_name`, `phone`, `email`, `source`, `register_ip`, `level`, `tags`, `consume_amount`, `intention_level`, `last_contact_time`, `notes`, `industry`, `assigned_to`) VALUES
('陈伟', '星辰科技有限公司', '138-0001-0001', 'chenwei@xingchen.com', '展会', '58.213.47.120', 'vip', '["重点客户","长期合作","高意向"]', 2850000.00, 'high', '2026-06-04', '公司年营收约5000万，CTO决策权大，对API性能和稳定性要求高', '互联网/电商', '张明'),
('李娜', '蓝鲸数据集团', '139-0002-0002', 'lina@bluewhale.com', '转介绍', '114.242.88.55', 'vip', '["战略合作","VIP客户"]', 3200000.00, 'high', '2026-06-05', '已签署战略合作协议，年度框架合同金额500w+', '大数据/AI', '李伟'),
('王强', '极光互娱网络科技', '137-0003-0003', 'wangqiang@aurora.com', '线上广告', '36.112.3.180', 'gold', '["游戏行业","高并发"]', 1250000.00, 'high', '2026-05-28', '游戏高峰期并发QPS需求达50万，对延迟敏感', '游戏/娱乐', '王芳'),
('赵敏', '云端教育科技', '136-0004-0004', 'zhaomin@cloudedu.cn', '合作伙伴推荐', '101.88.149.33', 'gold', '["教育","视频直播"]', 980000.00, 'medium', '2026-05-25', '主要使用视频相关API，正在评估AI内容审核方案', '在线教育', '张明'),
('刘洋', '锋行物流供应链', '135-0005-0005', 'liuyang@fenghang.com', '官网注册', '121.35.208.76', 'silver', '["物流","地图API"]', 560000.00, 'medium', '2026-06-01', '重度依赖地图和路径规划API，日均调用量50w+', '物流/供应链', '陈晨');

-- 报价示例数据
INSERT INTO `customer_quotes` (`customer_id`, `quote_title`, `quote_amount`, `quote_status`, `quote_content`, `currency`, `valid_until`) VALUES
(1, 'API性能优化方案报价', 180000.00, 'sent', '包含3个月技术支持和7x24监控', 'CNY', '2026-06-30'),
(1, 'Q3新增API接口开发', 250000.00, 'draft', '包括实时数据推送和批量处理接口', 'CNY', '2026-07-15'),
(2, '战略合作协议续签', 5500000.00, 'negotiating', '包含SLA 99.99%保障和专属客服经理', 'CNY', '2026-07-15'),
(3, '高并发解决方案', 350000.00, 'sent', '支持峰值QPS 50万+，P99延迟<200ms', 'CNY', '2026-06-25');

-- API产品示例数据
INSERT INTO `api_products` (`platform`, `api_name`, `api_path`, `price`, `status`, `old_price`, `currency`, `billing_unit`, `change_percent`, `effective_date`, `description`) VALUES
('微信支付', '统一下单', '/v3/pay/transactions/app', 0.0055, 'changed', 0.006, 'CNY', '每笔交易', -8.30, '2026-06-15', '微信支付下调商户费率'),
('百度AI', '文字识别OCR', '/rest/2.0/ocr/v1/general', 0.0035, 'changed', 0.004, 'CNY', '每次调用', -12.50, '2026-06-10', '通用文字识别价格下调'),
('抖音开放平台', '视频智能剪辑', '/video/intelligent/edit', 0.05, 'new', NULL, 'CNY', '每分钟视频', NULL, '2026-06-20', '新增视频智能剪辑API'),
('百度AI', '图像审核', '/rest/2.0/solution/v1/img_censor', 0.003, 'changed', 0.0025, 'CNY', '每次调用', 20.00, '2026-07-01', '图像审核升级多模态模型'),
('高德地图', '旧版地图SDK', '/v3/place/text', 0, 'deprecated', 0.01, 'CNY', '每次调用', -100.00, '2026-08-01', '旧版SDK即将下线，请迁移v4.0'),
('百度AI', '语音合成TTS', '/rest/2.0/tts/v1/synthesize', 0.008, 'new', NULL, 'CNY', '每千字符', NULL, '2026-06-18', '新增高品质语音合成API'),
('Stripe', '订阅管理', '/v1/subscriptions', 0.022, 'changed', 0.025, 'USD', '每笔订阅', -12.00, '2026-06-08', '订阅管理费率调整'),
('Stripe', '跨境支付', '/v1/payment_intents', 0.035, 'new', NULL, 'USD', '每笔交易', NULL, '2026-06-25', '新增亚太区跨境支付API'),
('腾讯云短信', '短信发送（营销）', '/v5/sms/marketing/send', 0.05, 'changed', 0.045, 'CNY', '每条', 11.10, '2026-07-01', '运营商资费调整，价格上调');
