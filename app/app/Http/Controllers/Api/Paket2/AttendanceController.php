<?php

namespace App\Http\Controllers\Api\Paket2;

use App\Http\Controllers\Api\Controller;
use App\Models\Paket2\Attendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Menampilkan daftar data Presensi dengan filter tanggal & pencarian NIM/Nama.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::query()->where('user_id', $request->user()->id);

        if ($request->filled('date')) {
            $query->where('date', $request->query('date'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhere('student_name', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 10);
        $attendances = $query->latest('id')->paginate($perPage);

        return $this->paginatedResponse($attendances);
    }

    /**
     * Menyimpan data Presensi baru.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nim' => 'required|string|max:50',
            'student_name' => 'required|string|max:150',
            'date' => 'required|string|date_format:d-m-Y',
            'time_in' => 'required|date_format:H:i:s',
            'time_out' => 'nullable|date_format:H:i:s',
            'status' => 'nullable|in:Hadir,Izin,Sakit,Alpha',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400, 'bad_request');
        }

        $attendance = Attendance::create([...$validator->validated(), 'user_id' => $request->user()->id]);

        return $this->successResponse($attendance, 201);
    }

    /**
     * Menampilkan detail satu data Presensi.
     *
     * @param  int|string  $id
     */
    public function show(Request $request, $id): JsonResponse
    {
        $attendance = Attendance::where('user_id', $request->user()->id)->find($id);

        if (! $attendance) {
            return $this->errorResponse(['attendance' => ['Attendance record not found']], 404, 'not_found');
        }

        return $this->successResponse($attendance);
    }

    /**
     * Memperbarui data Presensi (misal jam pulang / check-out atau koreksi status).
     *
     * @param  int|string  $id
     */
    public function update(Request $request, $id): JsonResponse
    {
        $attendance = Attendance::where('user_id', $request->user()->id)->find($id);

        if (! $attendance) {
            return $this->errorResponse(['attendance' => ['Attendance record not found']], 404, 'not_found');
        }

        $validator = Validator::make($request->all(), [
            'nim' => 'sometimes|required|string|max:50',
            'student_name' => 'sometimes|required|string|max:150',
            'date' => 'sometimes|required|string|date_format:d-m-Y',
            'time_in' => 'sometimes|required|date_format:H:i:s',
            'time_out' => 'nullable|date_format:H:i:s',
            'status' => 'nullable|in:Hadir,Izin,Sakit,Alpha',
            'note' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400, 'bad_request');
        }

        $attendance->update($validator->validated());

        return $this->successResponse($attendance);
    }

    /**
     * Menghapus data Presensi.
     *
     * @param  int|string  $id
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $attendance = Attendance::where('user_id', $request->user()->id)->find($id);

        if (! $attendance) {
            return $this->errorResponse(['attendance' => ['Attendance record not found']], 404, 'not_found');
        }

        $attendance->delete();

        return $this->successResponse(['message' => 'Attendance record deleted successfully']);
    }
}
