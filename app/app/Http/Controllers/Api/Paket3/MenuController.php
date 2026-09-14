<?php

namespace App\Http\Controllers\Api\Paket3;

use App\Http\Controllers\Api\Controller;
use App\Models\Paket3\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    /**
     * Menampilkan daftar data Menu Kafe dengan filter kategori & pencarian nama.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Menu::query();

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->has('is_available') && $request->query('is_available') !== '') {
            $query->where('is_available', filter_var($request->query('is_available'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = (int) $request->query('per_page', 10);
        $menus = $query->latest('id')->paginate($perPage);

        return $this->paginatedResponse($menus);
    }

    /**
     * Menyimpan data Menu baru.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:150',
            'category'     => 'nullable|in:Coffee,Non-Coffee,Snack,Main Course',
            'price'        => 'required|numeric|min:0',
            'stock'        => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            'description'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400, 'bad_request');
        }

        $menu = Menu::create($validator->validated());

        return $this->successResponse($menu, 201);
    }

    /**
     * Menampilkan detail satu data Menu.
     *
     * @param  int|string  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $menu = Menu::find($id);

        if (! $menu) {
            return $this->errorResponse(['menu' => ['Menu item not found']], 404, 'not_found');
        }

        return $this->successResponse($menu);
    }

    /**
     * Memperbarui data Menu yang sudah ada.
     *
     * @param  Request  $request
     * @param  int|string  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $menu = Menu::find($id);

        if (! $menu) {
            return $this->errorResponse(['menu' => ['Menu item not found']], 404, 'not_found');
        }

        $validator = Validator::make($request->all(), [
            'name'         => 'sometimes|required|string|max:150',
            'category'     => 'nullable|in:Coffee,Non-Coffee,Snack,Main Course',
            'price'        => 'sometimes|required|numeric|min:0',
            'stock'        => 'nullable|integer|min:0',
            'is_available' => 'nullable|boolean',
            'description'  => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400, 'bad_request');
        }

        $menu->update($validator->validated());

        return $this->successResponse($menu);
    }

    /**
     * Menghapus data Menu.
     *
     * @param  int|string  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $menu = Menu::find($id);

        if (! $menu) {
            return $this->errorResponse(['menu' => ['Menu item not found']], 404, 'not_found');
        }

        $menu->delete();

        return $this->successResponse(['message' => 'Menu item deleted successfully']);
    }
}
