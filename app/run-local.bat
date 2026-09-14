@echo off
setlocal enabledelayedexpansion
rem Jalankan aplikasi Laravel (soal-uts) di lokal.
rem Menyiapkan .env/APP_KEY/dependency kalau perlu, migrasi + seed database,
rem lalu start dev server (artisan serve + queue:listen + pail + vite) via `composer run dev`.
cd /d "%~dp0"

if not exist .env (
    echo ==^> .env tidak ditemukan, menyalin dari .env.example
    copy /y .env.example .env >nul
)

if not exist vendor (
    echo ==^> Menginstal dependency Composer
    call composer install
    if errorlevel 1 goto :error
)

findstr /b /c:"APP_KEY=base64" .env >nul
if errorlevel 1 (
    echo ==^> APP_KEY kosong, generate baru
    call php artisan key:generate
)

if not exist node_modules (
    echo ==^> Menginstal dependency npm
    call npm install
    if errorlevel 1 goto :error
)

set "DB_HOST="
set "DB_PORT="
for /f "tokens=2 delims==" %%A in ('findstr /b /c:"DB_HOST=" .env') do set "DB_HOST=%%A"
for /f "tokens=2 delims==" %%A in ('findstr /b /c:"DB_PORT=" .env') do set "DB_PORT=%%A"
if defined DB_HOST if defined DB_PORT (
    php -r "exit(@fsockopen('%DB_HOST%', (int) '%DB_PORT%', $errno, $errstr, 2) ? 0 : 1);" >nul 2>&1
    if errorlevel 1 (
        echo PERINGATAN: tidak bisa konek ke database %DB_HOST%:%DB_PORT%.
        echo             Pastikan MySQL sudah jalan ^(mis. start service MySQL di Laragon^).
    )
)

echo ==^> Menjalankan migrasi database
call php artisan migrate
if errorlevel 1 (
    echo PERINGATAN: migrasi gagal, cek koneksi/konfigurasi database di .env. Seeder dilewati.
) else (
    echo ==^> Menjalankan seeder ^(data dummy^)
    call php artisan db:seed
    if errorlevel 1 echo PERINGATAN: seeding gagal.
)

echo ==^> Menjalankan dev server ^(artisan serve + queue:listen + pail + vite^)
echo     Tekan Ctrl+C untuk berhenti.
call composer run dev

goto :eof

:error
echo Terjadi error saat instalasi dependency, proses dihentikan.
exit /b 1
