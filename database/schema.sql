-- Apply only inside the database selected by DB_NAME.
CREATE TABLE IF NOT EXISTS login (
    id VARCHAR(64) NOT NULL PRIMARY KEY,
    pw VARCHAR(255) NOT NULL,
    role ENUM('member', 'admin') NOT NULL DEFAULT 'member'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventory (
    id INT UNSIGNED NOT NULL PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Demo stock, unrelated to real inventory. Existing quantities are preserved.
INSERT IGNORE INTO inventory (id, product_name, quantity) VALUES
(1, '蜜汁烤雞腿飯', 100),
(2, '蔥油拌麵', 100),
(3, '泡菜炒飯', 100),
(4, '牛肉炒飯', 100),
(5, '豬肉炒飯', 100),
(6, '羊肉炒飯', 100),
(7, '黑椒牛柳飯', 100),
(8, '珍珠奶茶', 100),
(9, '奶茶', 100),
(10, '烏龍茶', 100),
(11, '東方美人茶', 100),
(12, '茉莉綠茶', 100),
(13, '烏龍奶蓋', 100),
(14, '紅茶奶蓋', 100),
(15, '雞塊', 100),
(16, '薯條', 100),
(17, '熱狗', 100),
(18, '洋蔥圈', 100);
