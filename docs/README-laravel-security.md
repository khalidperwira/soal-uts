# Panduan & Rekomendasi Keamanan API (Security Guidelines)

Dokumen ini memuat panduan, arsitektur keamanan, dan langkah-langkah implementasi (*security best practices*) untuk mengamankan backend REST API Laravel ini serta integrasinya dengan aplikasi client desktop (C#, Java, Python).

---

## 1. Autentikasi & Otorisasi Berbasis Token (Laravel Sanctum)

Menggunakan package `laravel/sanctum` untuk mengontrol hak akses ke seluruh endpoint data:

### a. Bearer Token Authorization
Setiap request ke endpoint terproteksi wajib menyertakan header:
```http
Authorization: Bearer <access_token>
Accept: application/json
```

### b. Token Expiration (Masa Berlaku Token)
Atur batas kedaluwarsa token pada file `config/sanctum.php` (misal: 120 menit) agar sesi token otomatis hangus setelah sesi ujian/praktikum selesai:
```php
'expiration' => 120, // masa berlaku dalam menit
```

### c. Token Revocation on Logout
Pastikan token dihapus dari database saat client melakukan aksi logout:
```php
$request->user()->currentAccessToken()->delete();
```

---

## 2. Rate Limiting & Proteksi Brute-Force (API Throttling)

Mencegah serangan Denial of Service (DoS) lokal, *looping request* tidak terkendali, atau *credential stuffing*:

### a. Global API Throttling
Menerapkan batasan maksimal 60 request per menit untuk setiap IP address pada grup rute API:
```php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('attendances', AttendanceController::class);
    Route::apiResource('menus', MenuController::class);
    Route::apiResource('tickets', TicketController::class);
});
```

### b. Strict Throttling untuk Endpoint Login
Membatasi percobaan login maksimal 5 kali per menit per IP untuk mencegah *brute-force*:
```php
Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
```

---

## 3. Validasi & Sanitasi Data Server-Side

Jangan pernah mempercayai input dari client desktop secara langsung:

### a. Strict Form Request Validation
Gunakan validasi ketat untuk tipe data, batas panjang karakter, format tanggal, serta whitelist nilai `enum`:
```php
$validated = $request->validate([
    'title'    => 'required|string|max:200',
    'category' => 'required|string|in:Kuliah,Pribadi,Pekerjaan',
    'status'   => 'required|in:pending,in_progress,completed',
    'due_date' => 'nullable|date_format:d-m-Y',
]);
```

### b. Mass Assignment Protection
Selalu definisikan properti `$fillable` secara eksplisit pada setiap Eloquent Model:
```php
protected $fillable = [
    'title',
    'description',
    'category',
    'status',
    'due_date',
];
```

### c. Pencegahan SQL Injection
Gunakan Eloquent ORM atau Query Builder dengan parameter binding. Hindari penggabungan raw SQL string langsung dari input request.

---

## 4. Keamanan Sisi Client Desktop

Aplikasi desktop (seperti `.exe` C#, `.jar` Java, `.pyc` Python) rentan terhadap *reverse engineering* / dekompilasi menggunakan alat seperti **dnSpy**, **JD-GUI**, atau **Ghidra**.

### Panduan Keamanan untuk Developer Desktop:
1. **Dilarang Hardcode Master Token / Kredensial Database**:
   - Jangan pernah menyematkan username/password database atau token super-admin langsung di dalam source code desktop.
   - Aplikasi desktop harus mendapatkan token dinamis melalui form login resmi.
2. **Penyimpanan Token yang Aman**:
   - Simpan token di memori aplikasi (*in-memory*) selama aplikasi aktif.
   - Jika perlu fitur *Remember Me*, gunakan enkripsi bawaan sistem operasi (seperti *Windows Data Protection API / DPAPI* atau *Credential Manager*), bukan menyimpan plain text pada file `.txt` atau `.ini`.
3. **Penanganan Respon Error API**:
   - Tangani respon HTTP error (400, 401, 404, 500) secara elegan di GUI (tampilkan pesan user-friendly tanpa menampilkan exception raw ke pengguna).

---

## 5. Transport Layer Security (Wajib HTTPS / TLS)

- **Enkripsi Jalur Komunikasi**:
  Gunakan protokol HTTPS/TLS untuk seluruh request API di lingkungan produksi atau server kampus.
- **Pencegahan Sniffing & MITM**:
  Mencegah penyadapan (*packet sniffing*) Bearer Token dan data sensitif pada jaringan Wi-Fi bersama atau jaringan lab komputer.

---

## 6. Mencegah Kebocoran Informasi Server (Information Disclosure)

### a. Nonaktifkan Mode Debug di Production
Ubah nilai `APP_DEBUG` menjadi `false` pada file `.env` ketika API siap digunakan oleh banyak peserta:
```env
APP_DEBUG=false
```

### b. Format Respon Error Terstandar
Pastikan error server 500 atau model not found (404) mengembalikan format JSON standar sesuai `docs/api-body.md` tanpa membocorkan trace query SQL atau path file server:
```json
{
  "code": 404,
  "status": "not_found",
  "errors": {
    "resource": [
      "Data yang diminta tidak ditemukan"
    ]
  }
}
```

---

## 7. Audit Trail & Logging

- **Logging Aktivitas Modifikasi**:
  Catat setiap operasi `POST`, `PUT`, dan `DELETE` ke log aplikasi (`storage/logs/laravel.log`) yang memuat timestamp, IP client, User ID, dan entitas yang diubah:
  ```php
  Log::info('Task modified', [
      'user_id' => auth()->id() ?? 'guest',
      'ip'      => request()->ip(),
      'action'  => 'update',
      'task_id' => $task->id,
  ]);
  ```
- **Monitoring Log Real-time**:
  Gunakan `php artisan pail` untuk memantau trafik dan potensi aktivitas mencurigakan saat ujian berlangsung.
