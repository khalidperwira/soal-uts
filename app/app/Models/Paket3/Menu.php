<?php

namespace App\Models\Paket3;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'menus';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'category',
        'price',
        'stock',
        'is_available',
        'description',
    ];

    /**
     * Nilai default untuk atribut model.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'category' => 'Coffee',
        'stock' => 0,
        'is_available' => true,
    ];

    /**
     * Definisi type casting atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'float',
            'stock' => 'integer',
            'is_available' => 'boolean',
        ];
    }
}
