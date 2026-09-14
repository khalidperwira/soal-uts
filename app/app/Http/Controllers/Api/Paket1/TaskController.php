<?php

namespace App\Http\Controllers\Api\Paket1;

use App\Http\Controllers\Api\Controller;
use App\Models\Paket1\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Menampilkan daftar data Task dengan filter & paginasi.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Task::query();

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->query('per_page', 10);
        $tasks = $query->latest('id')->paginate($perPage);

        return $this->paginatedResponse($tasks);
    }

    /**
     * Menyimpan data Task baru.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:100',
            'status'      => 'nullable|in:pending,in_progress,completed',
            'due_date'    => 'nullable|string|date_format:d-m-Y',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400, 'bad_request');
        }

        $task = Task::create($validator->validated());

        return $this->successResponse($task, 201);
    }

    /**
     * Menampilkan detail satu data Task.
     *
     * @param  int|string  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $task = Task::find($id);

        if (! $task) {
            return $this->errorResponse(['task' => ['Task not found']], 404, 'not_found');
        }

        return $this->successResponse($task);
    }

    /**
     * Memperbarui data Task yang sudah ada.
     *
     * @param  Request  $request
     * @param  int|string  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $task = Task::find($id);

        if (! $task) {
            return $this->errorResponse(['task' => ['Task not found']], 404, 'not_found');
        }

        $validator = Validator::make($request->all(), [
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'nullable|string|max:100',
            'status'      => 'nullable|in:pending,in_progress,completed',
            'due_date'    => 'nullable|string|date_format:d-m-Y',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400, 'bad_request');
        }

        $task->update($validator->validated());

        return $this->successResponse($task);
    }

    /**
     * Menghapus data Task.
     *
     * @param  int|string  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $task = Task::find($id);

        if (! $task) {
            return $this->errorResponse(['task' => ['Task not found']], 404, 'not_found');
        }

        $task->delete();

        return $this->successResponse(['message' => 'Task deleted successfully']);
    }
}
