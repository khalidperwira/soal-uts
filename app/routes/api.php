<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Paket1\TaskController;
use App\Http\Controllers\Api\Paket2\AttendanceController;
use App\Http\Controllers\Api\Paket3\MenuController;
use App\Http\Controllers\Api\Paket4\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rute Autentikasi Publik (Rate Limit: 60 request / menit)
Route::prefix('auth')->middleware('throttle:60,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Rute CRUD Terproteksi Sanctum (Rate Limit: env('API_RATE_LIMIT', 300) request / menit)
Route::middleware(['auth:sanctum', 'throttle:'.env('API_RATE_LIMIT', 300).',1'])->group(function () {
    // Auth status & revoke token
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);

    // Paket 1: Personal To-Do & Task Manager
    Route::apiResource('tasks', TaskController::class);

    // Paket 2: Sistem Presensi & Kehadiran Mahasiswa/Karyawan
    Route::apiResource('attendances', AttendanceController::class);

    // Paket 3: Sistem Kasir & Katalog Menu Kafe
    Route::apiResource('menus', MenuController::class);

    // Paket 4: Sistem Antrean Loket Pelayanan & Tiket
    Route::apiResource('tickets', TicketController::class);
});
