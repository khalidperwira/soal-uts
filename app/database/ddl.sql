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


-- ==========================================================
-- DDL SQL: Paket 2 - Sistem Presensi Kehadiran Mahasiswa/Karyawan
-- File: database/paket-2.sql
-- Format Tanggal: DD-MM-YYYY (contoh: 10-09-2026)
-- ==========================================================

-- 1. Membuat Tabel `attendances`
CREATE TABLE IF NOT EXISTS `attendances` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nim` VARCHAR(50) NOT NULL,
    `student_name` VARCHAR(150) NOT NULL,
    `date` VARCHAR(10) NOT NULL COMMENT 'Format: DD-MM-YYYY',
    `time_in` TIME NOT NULL,
    `time_out` TIME NULL,
    `status` ENUM('Hadir', 'Izin', 'Sakit', 'Alpha') NOT NULL DEFAULT 'Hadir',
    `note` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Data Dummy Awal untuk Pengujian (Seeder/Sample Data)
INSERT INTO `attendances` (`nim`, `student_name`, `date`, `time_in`, `time_out`, `status`, `note`, `created_at`, `updated_at`) VALUES
('20260801001', 'Ahmad Fauzi', '10-09-2026', '07:45:00', '16:05:00', 'Hadir', 'Tepat waktu', NOW(), NOW()),
('20260801002', 'Budi Santoso', '10-09-2026', '08:15:00', NULL, 'Hadir', 'Praktikum sesi pagi', NOW(), NOW()),
('20260801003', 'Citra Dewi', '10-09-2026', '08:00:00', NULL, 'Izin', 'Menghadiri lomba kampus', NOW(), NOW()),
('20260801004', 'Deni Setiawan', '10-09-2026', '08:00:00', NULL, 'Sakit', 'Surat dokter terlampir', NOW(), NOW()),
('20260801005', 'Eka Rahmawati', '10-09-2026', '07:50:00', '16:00:00', 'Hadir', 'Tepat waktu', NOW(), NOW());


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


-- ==========================================================
-- DDL SQL: Paket 4 - Sistem Antrean Loket Pelayanan & Helpdesk Tiket
-- File: database/paket-4.sql
-- ==========================================================

-- 1. Membuat Tabel `tickets`
CREATE TABLE IF NOT EXISTS `tickets` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_number` VARCHAR(20) NOT NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `service_type` ENUM('Customer Service', 'Teller', 'Helpdesk') NOT NULL DEFAULT 'Customer Service',
    `status` ENUM('Menunggu', 'Dipanggil', 'Selesai', 'Batal') NOT NULL DEFAULT 'Menunggu',
    `counter_number` VARCHAR(10) NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Data Dummy Awal untuk Pengujian (Seeder/Sample Data)
INSERT INTO `tickets` (`ticket_number`, `customer_name`, `service_type`, `status`, `counter_number`, `notes`, `created_at`, `updated_at`) VALUES
('A-001', 'Budi Santoso', 'Customer Service', 'Dipanggil', 'Loket 1', 'Konsultasi aktivasi akun portal mahasiswa', NOW(), NOW()),
('A-002', 'Siti Nurhaliza', 'Customer Service', 'Menunggu', NULL, 'Pengajuan surat keterangan aktif kuliah', NOW(), NOW()),
('B-001', 'Rian Hidayat', 'Teller', 'Selesai', 'Loket 2', 'Pembayaran biaya praktikum & wisuda', NOW(), NOW()),
('B-002', 'Dewi Lestari', 'Teller', 'Menunggu', NULL, 'Validasi slip pembayaran SPP semester ganjil', NOW(), NOW()),
('C-001', 'Fajar Ramadhan', 'Helpdesk', 'Menunggu', NULL, 'Reset password email institusi dan Wi-Fi kampus', NOW(), NOW());
