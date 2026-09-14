<?php

namespace Database\Seeders;

use App\Models\Paket1\Task;
use App\Models\Paket2\Attendance;
use App\Models\Paket3\Menu;
use App\Models\Paket4\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Data Dummy Users (Autentikasi Sanctum)
        $users = [
            [
                'name' => 'Admin Penguji UTS',
                'email' => 'admin@uts.test',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@uts.test',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti@uts.test',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi@uts.test',
                'password' => Hash::make('password123'),
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@uts.test',
                'password' => Hash::make('password123'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        // Data dummy Paket 1-4 di bawah ini dimiliki oleh akun admin,
        // supaya kolom user_id (isolasi data per siswa) tetap terisi.
        $ownerId = User::where('email', 'admin@uts.test')->value('id');

        // 2. Data Dummy Paket 1: Tasks (Personal To-Do & Task Manager)
        $tasks = [
            [
                'title' => 'Menyelesaikan Laporan Praktikum Jaringan Komputer',
                'description' => 'Mengerjakan analisis konfigurasi router dan routing OSPF Bab 1 sampai Bab 4.',
                'category' => 'Kuliah',
                'status' => 'in_progress',
                'due_date' => '15-09-2026',
            ],
            [
                'title' => 'Mengerjakan Proyek Aplikasi Desktop UTS',
                'description' => 'Membuat antarmuka CRUD dan integrasi API REST dengan token Bearer Sanctum.',
                'category' => 'Kuliah',
                'status' => 'pending',
                'due_date' => '20-09-2026',
            ],
            [
                'title' => 'Membeli Perlengkapan Modul IoT Mikrokontroler',
                'description' => 'Sensor suhu DHT22, jumper wire, breadboard, dan modul board ESP32.',
                'category' => 'Pribadi',
                'status' => 'completed',
                'due_date' => '05-09-2026',
            ],
            [
                'title' => 'Revisi Makalah Kecerdasan Buatan',
                'description' => 'Penambahan tabel akurasi dan visualisasi confusion matrix model CNN.',
                'category' => 'Kuliah',
                'status' => 'pending',
                'due_date' => '18-09-2026',
            ],
            [
                'title' => 'Backup Source Code ke Repositori GitHub',
                'description' => 'Push commit terbaru ke branch main dan membuat tagging release v1.0.',
                'category' => 'Pekerjaan',
                'status' => 'completed',
                'due_date' => '08-09-2026',
            ],
        ];

        foreach ($tasks as $taskData) {
            Task::updateOrCreate(
                ['title' => $taskData['title']],
                [...$taskData, 'user_id' => $ownerId]
            );
        }

        // 3. Data Dummy Paket 2: Attendances (Sistem Presensi & Kehadiran)
        $attendances = [
            [
                'nim' => '230101001',
                'student_name' => 'Ahmad Rizki',
                'date' => '11-09-2026',
                'time_in' => '07:45:00',
                'time_out' => '16:05:00',
                'status' => 'Hadir',
                'note' => 'Hadir tepat waktu di laboratorium komputer',
            ],
            [
                'nim' => '230101002',
                'student_name' => 'Budi Santoso',
                'date' => '11-09-2026',
                'time_in' => '07:55:00',
                'time_out' => '16:00:00',
                'status' => 'Hadir',
                'note' => null,
            ],
            [
                'nim' => '230101003',
                'student_name' => 'Citra Dewi',
                'date' => '11-09-2026',
                'time_in' => '08:15:00',
                'time_out' => null,
                'status' => 'Izin',
                'note' => 'Izin mengikuti kegiatan seminar nasional di luar kampus',
            ],
            [
                'nim' => '230101004',
                'student_name' => 'Dimas Pratama',
                'date' => '11-09-2026',
                'time_in' => '08:00:00',
                'time_out' => null,
                'status' => 'Sakit',
                'note' => 'Surat keterangan dokter terlampir',
            ],
            [
                'nim' => '230101005',
                'student_name' => 'Eka Putri',
                'date' => '11-09-2026',
                'time_in' => '07:50:00',
                'time_out' => '16:10:00',
                'status' => 'Hadir',
                'note' => null,
            ],
        ];

        foreach ($attendances as $attData) {
            Attendance::updateOrCreate(
                ['nim' => $attData['nim'], 'date' => $attData['date']],
                [...$attData, 'user_id' => $ownerId]
            );
        }

        // 4. Data Dummy Paket 3: Menus (Sistem Kasir & Katalog Menu Kafe)
        $menus = [
            [
                'name' => 'Espresso Single Shot',
                'category' => 'Coffee',
                'price' => 18000,
                'stock' => 50,
                'is_available' => true,
                'description' => 'Ekstraksi kopi murni biji Arabika pilihan single origin 30ml.',
            ],
            [
                'name' => 'Caramel Macchiato Ice',
                'category' => 'Coffee',
                'price' => 28000,
                'stock' => 35,
                'is_available' => true,
                'description' => 'Espresso dengan susu segar creamy dan saus karamel premium.',
            ],
            [
                'name' => 'Matcha Latte Ice',
                'category' => 'Non-Coffee',
                'price' => 26000,
                'stock' => 25,
                'is_available' => true,
                'description' => 'Teh hijau Jepang murni dipadukan dengan fresh milk lembut.',
            ],
            [
                'name' => 'French Fries Crispy',
                'category' => 'Snack',
                'price' => 20000,
                'stock' => 40,
                'is_available' => true,
                'description' => 'Kentang goreng renyah disajikan dengan saus keju dan sambal.',
            ],
            [
                'name' => 'Nasi Goreng Spesial Kafe',
                'category' => 'Main Course',
                'price' => 35000,
                'stock' => 20,
                'is_available' => true,
                'description' => 'Nasi goreng bumbu rempah dengan telur mata sapi, sate ayam, dan kerupuk.',
            ],
        ];

        foreach ($menus as $menuData) {
            Menu::updateOrCreate(
                ['name' => $menuData['name']],
                [...$menuData, 'user_id' => $ownerId]
            );
        }

        // 5. Data Dummy Paket 4: Tickets (Sistem Antrean Loket Pelayanan)
        $tickets = [
            [
                'ticket_number' => 'CS-001',
                'customer_name' => 'Fajar Nugraha',
                'service_type' => 'Customer Service',
                'status' => 'Selesai',
                'counter_number' => 'Loket 1',
                'notes' => 'Konsultasi aktivasi dan verifikasi akun layanan baru.',
            ],
            [
                'ticket_number' => 'TL-002',
                'customer_name' => 'Gita Gutawa',
                'service_type' => 'Teller',
                'status' => 'Dipanggil',
                'counter_number' => 'Loket 2',
                'notes' => 'Transaksi setoran tunai pembayaran biaya kuliah.',
            ],
            [
                'ticket_number' => 'HD-003',
                'customer_name' => 'Hendra Saputra',
                'service_type' => 'Helpdesk',
                'status' => 'Menunggu',
                'counter_number' => null,
                'notes' => 'Kendala reset password akun sistem informasi akademik.',
            ],
            [
                'ticket_number' => 'CS-004',
                'customer_name' => 'Indah Permata',
                'service_type' => 'Customer Service',
                'status' => 'Menunggu',
                'counter_number' => null,
                'notes' => 'Klaim garansi dan penggantian kartu akses lab.',
            ],
            [
                'ticket_number' => 'TL-005',
                'customer_name' => 'Joko Susilo',
                'service_type' => 'Teller',
                'status' => 'Batal',
                'counter_number' => null,
                'notes' => 'Pelanggan membatalkan antrean karena dokumen belum lengkap.',
            ],
        ];

        foreach ($tickets as $ticketData) {
            Ticket::updateOrCreate(
                ['ticket_number' => $ticketData['ticket_number']],
                [...$ticketData, 'user_id' => $ownerId]
            );
        }
    }
}
