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
