<?php

namespace App\Http\Controllers\Api\Paket4;

use App\Http\Controllers\Api\Controller;
use App\Models\Paket4\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    /**
     * Menampilkan daftar data Antrean Tiket dengan filter status & jenis layanan.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ticket::query()->where('user_id', $request->user()->id);

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->query('service_type'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 10);
        $tickets = $query->latest('id')->paginate($perPage);

        return $this->paginatedResponse($tickets);
    }

    /**
     * Menyimpan data Antrean Tiket baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'ticket_number' => 'required|string|max:20',
            'customer_name' => 'required|string|max:150',
            'service_type' => 'nullable|in:Customer Service,Teller,Helpdesk',
            'status' => 'nullable|in:Menunggu,Dipanggil,Selesai,Batal',
            'counter_number' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400, 'bad_request');
        }

        $ticket = Ticket::create([...$validator->validated(), 'user_id' => $request->user()->id]);

        return $this->successResponse($ticket, 201);
    }

    /**
     * Menampilkan detail satu data Antrean Tiket.
     *
     * @param  int|string  $id
     */
    public function show(Request $request, $id): JsonResponse
    {
        $ticket = Ticket::where('user_id', $request->user()->id)->find($id);

        if (! $ticket) {
            return $this->errorResponse(['ticket' => ['Ticket not found']], 404, 'not_found');
        }

        return $this->successResponse($ticket);
    }

    /**
     * Memperbarui data Antrean Tiket (misal panggil ke loket / ubah status / catatan).
     *
     * @param  int|string  $id
     */
    public function update(Request $request, $id): JsonResponse
    {
        $ticket = Ticket::where('user_id', $request->user()->id)->find($id);

        if (! $ticket) {
            return $this->errorResponse(['ticket' => ['Ticket not found']], 404, 'not_found');
        }

        $validator = Validator::make($request->all(), [
            'ticket_number' => 'sometimes|required|string|max:20',
            'customer_name' => 'sometimes|required|string|max:150',
            'service_type' => 'nullable|in:Customer Service,Teller,Helpdesk',
            'status' => 'nullable|in:Menunggu,Dipanggil,Selesai,Batal',
            'counter_number' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400, 'bad_request');
        }

        $ticket->update($validator->validated());

        return $this->successResponse($ticket);
    }

    /**
     * Menghapus data Antrean Tiket.
     *
     * @param  int|string  $id
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $ticket = Ticket::where('user_id', $request->user()->id)->find($id);

        if (! $ticket) {
            return $this->errorResponse(['ticket' => ['Ticket not found']], 404, 'not_found');
        }

        $ticket->delete();

        return $this->successResponse(['message' => 'Ticket deleted successfully']);
    }
}
