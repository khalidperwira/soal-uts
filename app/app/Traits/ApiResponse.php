<?php

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Format response JSON sukses standar untuk single object atau data umum.
     *
     * @param  mixed  $data
     * @param  int  $code
     * @param  string  $status
     * @return JsonResponse
     */
    public function successResponse(mixed $data = null, int $code = 200, string $status = 'success'): JsonResponse
    {
        return response()->json([
            'code'   => $code,
            'status' => $status,
            'body'   => $data,
        ], $code);
    }

    /**
     * Format response JSON sukses standar dengan paginasi.
     *
     * @param  LengthAwarePaginator  $paginator
     * @param  int  $code
     * @param  string  $status
     * @return JsonResponse
     */
    public function paginatedResponse(LengthAwarePaginator $paginator, int $code = 200, string $status = 'success'): JsonResponse
    {
        return response()->json([
            'code'   => $code,
            'status' => $status,
            'body'   => $paginator->items(),
            'page'   => [
                'size'      => (int) $paginator->perPage(),
                'totalPage' => (int) $paginator->lastPage(),
                'total'     => (int) $paginator->total(),
                'current'   => (int) $paginator->currentPage(),
            ],
        ], $code);
    }

    /**
     * Format response JSON error standar.
     *
     * @param  mixed  $errors
     * @param  int  $code
     * @param  string  $status
     * @return JsonResponse
     */
    public function errorResponse(mixed $errors = [], int $code = 400, string $status = 'bad_request'): JsonResponse
    {
        // Konversi objek Arrayable atau MessageBag ke array jika diperlukan
        if ($errors instanceof Arrayable) {
            $errors = $errors->toArray();
        } elseif (is_string($errors)) {
            $errors = ['message' => [$errors]];
        }

        return response()->json([
            'code'   => $code,
            'status' => $status,
            'errors' => $errors,
        ], $code);
    }
}
