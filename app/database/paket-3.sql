-- ==========================================================
-- DDL SQL: Paket 3 - Sistem Kasir & Manajemen Menu Kafe (Cafe POS & Menu)
-- File: database/paket-3.sql
-- ==========================================================

-- 1. Membuat Tabel `menus`
CREATE TABLE IF NOT EXISTS `menus` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `category` ENUM('Coffee', 'Non-Coffee', 'Snack', 'Main Course') NOT NULL DEFAULT 'Coffee',
    `price` DECIMAL(12, 2) NOT NULL,
    `stock` INT NOT NULL DEFAULT 0,
    `is_available` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Tersedia, 0 = Habis',
    `description` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Data Dummy Awal untuk Pengujian (Seeder/Sample Data)
INSERT INTO `menus` (`name`, `category`, `price`, `stock`, `is_available`, `description`, `created_at`, `updated_at`) VALUES
('Caramel Macchiato', 'Coffee', 28000.00, 45, 1, 'Espresso segar dipadukan susu creamy dan saus karamel manis gurih', NOW(), NOW()),
('Americano Double Shot', 'Coffee', 20000.00, 60, 1, 'Kopi hitam murni dari biji arabika pilihan tanpa gula', NOW(), NOW()),
('Matcha Green Tea Latte', 'Non-Coffee', 25000.00, 30, 1, 'Bubuk matcha murni dari Jepang dengan susu steamed lembut', NOW(), NOW()),
('French Fries Sea Salt', 'Snack', 18000.00, 50, 1, 'Kentang goreng renyah dengan taburan garam laut dan saus cocolan', NOW(), NOW()),
('Spaghetti Carbonara', 'Main Course', 35000.00, 20, 1, 'Pasta spaghetti dengan saus krim keju, potongan smoked beef, dan oregano', NOW(), NOW());
