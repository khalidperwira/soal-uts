-- ==========================================================
-- DDL SQL: Paket 1 - Personal To-Do & Task Manager
-- File: database/paket-1.sql
-- Format Tanggal: DD-MM-YYYY (contoh: 15-09-2026)
-- ==========================================================

-- 1. Membuat Tabel `tasks`
CREATE TABLE IF NOT EXISTS `tasks` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `category` VARCHAR(100) NOT NULL DEFAULT 'Kuliah',
    `status` ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    `due_date` VARCHAR(10) NULL COMMENT 'Format: DD-MM-YYYY',
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Data Dummy Awal untuk Pengujian (Seeder/Sample Data)
INSERT INTO `tasks` (`title`, `description`, `category`, `status`, `due_date`, `created_at`, `updated_at`) VALUES
('Mengerjakan Laporan Praktikum', 'Modul 4 Integrasi REST API Desktop', 'Kuliah', 'in_progress', '15-09-2026', NOW(), NOW()),
('Belajar Persiapan UTS', 'Materi Pemrograman Berorientasi Objek & GUI', 'Kuliah', 'pending', '18-09-2026', NOW(), NOW()),
('Service Motor Rutin', 'Ganti oli mesin dan cek rem ke bengkel resmi', 'Pribadi', 'completed', '10-09-2026', NOW(), NOW()),
('Menyiapkan Slide Presentasi', 'Presentasi tugas besar kelompok di lab komputer', 'Kuliah', 'pending', '20-09-2026', NOW(), NOW()),
('Membeli Buku Referensi C# & Java', 'Mencari buku pemrograman GUI desktop di toko buku', 'Pribadi', 'completed', '05-09-2026', NOW(), NOW());
