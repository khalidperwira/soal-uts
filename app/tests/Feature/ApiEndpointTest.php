<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiEndpointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Helper untuk mendapatkan Bearer Token aktif dari login.
     */
    protected function authenticate(): array
    {
        $user = User::factory()->create([
            'email' => 'tester_'.uniqid().'@uts.test',
            'password' => bcrypt('password123'),
        ]);

        $token = $user->createToken('test_token')->plainTextToken;

        return [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];
    }

    /**
     * Request tanpa token ke endpoint terproteksi harus 401 (envelope standar),
     * baik dengan maupun tanpa header Accept: application/json — bukan 500.
     */
    public function test_unauthenticated_request_returns_401_envelope(): void
    {
        foreach ([[], ['Accept' => 'application/json']] as $headers) {
            $this->withHeaders($headers)
                ->get('/api/tasks')
                ->assertStatus(401)
                ->assertExactJson([
                    'code' => 401,
                    'status' => 'unauthorized',
                    'errors' => ['message' => ['Unauthenticated.']],
                ]);

            $this->withHeaders($headers)
                ->get('/api/auth/me')
                ->assertStatus(401)
                ->assertJsonPath('status', 'unauthorized');
        }
    }

    /**
     * 1. Test Autentikasi: Register, Login, Me, Logout.
     */
    public function test_auth_endpoints(): void
    {
        // 1.1 Register
        $regResponse = $this->postJson('/api/auth/register', [
            'name' => 'Test Auth User',
            'email' => 'authuser_'.uniqid().'@uts.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $regResponse->assertStatus(201)
            ->assertJsonStructure([
                'code',
                'status',
                'body' => ['user', 'token'],
            ]);

        // 1.2 Login
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $regResponse->json('body.user.email'),
            'password' => 'password123',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJson(['code' => 200, 'status' => 'success'])
            ->assertJsonStructure(['body' => ['token']]);

        $token = $loginResponse->json('body.token');
        $headers = [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ];

        // 1.3 Me (Profile)
        $meResponse = $this->getJson('/api/auth/me', $headers);
        $meResponse->assertStatus(200)
            ->assertJson(['code' => 200, 'status' => 'success']);

        // 1.4 Logout
        $logoutResponse = $this->postJson('/api/auth/logout', [], $headers);
        $logoutResponse->assertStatus(200)
            ->assertJson(['code' => 200, 'status' => 'success']);
    }

    /**
     * 2. Test CRUD Paket 1: Tasks.
     */
    public function test_tasks_crud(): void
    {
        $headers = $this->authenticate();

        // 2.1 CREATE
        $createResponse = $this->postJson('/api/tasks', [
            'title' => 'Tugas Baru Testing',
            'description' => 'Deskripsi untuk pengujian otomatis',
            'category' => 'Kuliah',
            'status' => 'pending',
            'due_date' => '25-09-2026',
        ], $headers);

        $createResponse->assertStatus(201)
            ->assertJson(['code' => 201, 'status' => 'success'])
            ->assertJsonPath('body.title', 'Tugas Baru Testing');

        $taskId = $createResponse->json('body.id');

        // 2.2 READ ALL (List & Pagination)
        $listResponse = $this->getJson('/api/tasks', $headers);
        $listResponse->assertStatus(200)
            ->assertJsonStructure([
                'code',
                'status',
                'body',
                'page' => ['size', 'totalPage', 'total', 'current'],
            ]);

        // 2.3 READ DETAIL
        $showResponse = $this->getJson("/api/tasks/{$taskId}", $headers);
        $showResponse->assertStatus(200)
            ->assertJsonPath('body.id', $taskId);

        // 2.4 UPDATE
        $updateResponse = $this->putJson("/api/tasks/{$taskId}", [
            'status' => 'completed',
        ], $headers);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('body.status', 'completed');

        // 2.5 DELETE
        $deleteResponse = $this->deleteJson("/api/tasks/{$taskId}", [], $headers);
        $deleteResponse->assertStatus(200)
            ->assertJson(['code' => 200, 'status' => 'success']);
    }

    /**
     * 3. Test CRUD Paket 2: Attendances.
     */
    public function test_attendances_crud(): void
    {
        $headers = $this->authenticate();

        // 3.1 CREATE
        $createResponse = $this->postJson('/api/attendances', [
            'nim' => '239999001',
            'student_name' => 'Mahasiswa Testing',
            'date' => '11-09-2026',
            'time_in' => '08:00:00',
            'status' => 'Hadir',
            'note' => 'Catatan presensi test',
        ], $headers);

        $createResponse->assertStatus(201)
            ->assertJson(['code' => 201, 'status' => 'success'])
            ->assertJsonPath('body.nim', '239999001');

        $attId = $createResponse->json('body.id');

        // 3.2 READ ALL
        $listResponse = $this->getJson('/api/attendances?search=239999001', $headers);
        $listResponse->assertStatus(200)
            ->assertJsonStructure(['code', 'status', 'body', 'page']);

        // 3.3 READ DETAIL
        $showResponse = $this->getJson("/api/attendances/{$attId}", $headers);
        $showResponse->assertStatus(200)
            ->assertJsonPath('body.id', $attId);

        // 3.4 UPDATE (Check-out)
        $updateResponse = $this->putJson("/api/attendances/{$attId}", [
            'time_out' => '17:00:00',
        ], $headers);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('body.time_out', '17:00:00');

        // 3.5 DELETE
        $deleteResponse = $this->deleteJson("/api/attendances/{$attId}", [], $headers);
        $deleteResponse->assertStatus(200)
            ->assertJson(['code' => 200, 'status' => 'success']);
    }

    /**
     * 4. Test CRUD Paket 3: Menus.
     */
    public function test_menus_crud(): void
    {
        $headers = $this->authenticate();

        // 4.1 CREATE
        $createResponse = $this->postJson('/api/menus', [
            'name' => 'Caffe Mocha Testing',
            'category' => 'Coffee',
            'price' => 30000,
            'stock' => 50,
            'is_available' => true,
            'description' => 'Espresso dengan cokelat dan susu',
        ], $headers);

        $createResponse->assertStatus(201)
            ->assertJson(['code' => 201, 'status' => 'success'])
            ->assertJsonPath('body.name', 'Caffe Mocha Testing');

        $menuId = $createResponse->json('body.id');

        // 4.2 READ ALL
        $listResponse = $this->getJson('/api/menus?category=Coffee', $headers);
        $listResponse->assertStatus(200)
            ->assertJsonStructure(['code', 'status', 'body', 'page']);

        // 4.3 READ DETAIL
        $showResponse = $this->getJson("/api/menus/{$menuId}", $headers);
        $showResponse->assertStatus(200)
            ->assertJsonPath('body.id', $menuId);

        // 4.4 UPDATE (Price & Stock)
        $updateResponse = $this->putJson("/api/menus/{$menuId}", [
            'price' => 32000,
            'stock' => 45,
        ], $headers);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('body.price', 32000)
            ->assertJsonPath('body.stock', 45);

        // 4.5 DELETE
        $deleteResponse = $this->deleteJson("/api/menus/{$menuId}", [], $headers);
        $deleteResponse->assertStatus(200)
            ->assertJson(['code' => 200, 'status' => 'success']);
    }

    /**
     * 5. Test CRUD Paket 4: Tickets.
     */
    public function test_tickets_crud(): void
    {
        $headers = $this->authenticate();

        // 5.1 CREATE
        $createResponse = $this->postJson('/api/tickets', [
            'ticket_number' => 'TS-999',
            'customer_name' => 'Pelanggan Test',
            'service_type' => 'Customer Service',
            'status' => 'Menunggu',
            'notes' => 'Antrean baru',
        ], $headers);

        $createResponse->assertStatus(201)
            ->assertJson(['code' => 201, 'status' => 'success'])
            ->assertJsonPath('body.ticket_number', 'TS-999');

        $ticketId = $createResponse->json('body.id');

        // 5.2 READ ALL
        $listResponse = $this->getJson('/api/tickets?status=Menunggu', $headers);
        $listResponse->assertStatus(200)
            ->assertJsonStructure(['code', 'status', 'body', 'page']);

        // 5.3 READ DETAIL
        $showResponse = $this->getJson("/api/tickets/{$ticketId}", $headers);
        $showResponse->assertStatus(200)
            ->assertJsonPath('body.id', $ticketId);

        // 5.4 UPDATE (Panggil Loket)
        $updateResponse = $this->putJson("/api/tickets/{$ticketId}", [
            'counter_number' => 'Loket 3',
            'status' => 'Dipanggil',
        ], $headers);

        $updateResponse->assertStatus(200)
            ->assertJsonPath('body.counter_number', 'Loket 3')
            ->assertJsonPath('body.status', 'Dipanggil');

        // 5.5 DELETE
        $deleteResponse = $this->deleteJson("/api/tickets/{$ticketId}", [], $headers);
        $deleteResponse->assertStatus(200)
            ->assertJson(['code' => 200, 'status' => 'success']);
    }

    /**
     * 6. Test Isolasi Data: siswa A tidak boleh baca/ubah/hapus data siswa B.
     */
    public function test_data_isolation_between_users(): void
    {
        $headersA = $this->authenticate();
        $headersB = $this->authenticate();

        // Siswa A membuat data di keempat paket.
        $taskId = $this->postJson('/api/tasks', [
            'title' => 'Tugas Milik Siswa A',
        ], $headersA)->json('body.id');

        $attendanceId = $this->postJson('/api/attendances', [
            'nim' => '239999002',
            'student_name' => 'Siswa A',
            'date' => '11-09-2026',
            'time_in' => '08:00:00',
        ], $headersA)->json('body.id');

        $menuId = $this->postJson('/api/menus', [
            'name' => 'Menu Milik Siswa A',
            'price' => 10000,
        ], $headersA)->json('body.id');

        $ticketId = $this->postJson('/api/tickets', [
            'ticket_number' => 'ISO-001',
            'customer_name' => 'Pelanggan Siswa A',
        ], $headersA)->json('body.id');

        // Sanctum meng-cache user hasil resolve guard per-instance; reset dulu
        // supaya panggilan berikutnya benar-benar diautentikasi ulang sebagai siswa B.
        $this->app['auth']->forgetGuards();

        // Siswa B tidak boleh melihat data siswa A di daftar index.
        $this->getJson('/api/tasks', $headersB)
            ->assertJsonMissing(['id' => $taskId]);

        // Siswa B tidak boleh show/update/delete data siswa A -> harus 404, bukan 200/403.
        $this->getJson("/api/tasks/{$taskId}", $headersB)->assertStatus(404);
        $this->putJson("/api/tasks/{$taskId}", ['title' => 'Diubah Siswa B'], $headersB)->assertStatus(404);
        $this->deleteJson("/api/tasks/{$taskId}", [], $headersB)->assertStatus(404);

        $this->getJson("/api/attendances/{$attendanceId}", $headersB)->assertStatus(404);
        $this->putJson("/api/attendances/{$attendanceId}", ['time_out' => '17:00:00'], $headersB)->assertStatus(404);
        $this->deleteJson("/api/attendances/{$attendanceId}", [], $headersB)->assertStatus(404);

        $this->getJson("/api/menus/{$menuId}", $headersB)->assertStatus(404);
        $this->putJson("/api/menus/{$menuId}", ['price' => 5000], $headersB)->assertStatus(404);
        $this->deleteJson("/api/menus/{$menuId}", [], $headersB)->assertStatus(404);

        $this->getJson("/api/tickets/{$ticketId}", $headersB)->assertStatus(404);
        $this->putJson("/api/tickets/{$ticketId}", ['status' => 'Batal'], $headersB)->assertStatus(404);
        $this->deleteJson("/api/tickets/{$ticketId}", [], $headersB)->assertStatus(404);

        // Data siswa A tetap aman dan masih bisa diakses oleh siswa A sendiri.
        $this->app['auth']->forgetGuards();
        $this->getJson("/api/tasks/{$taskId}", $headersA)->assertStatus(200);
    }
}
