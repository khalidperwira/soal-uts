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
