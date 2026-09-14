<?php

namespace App\Models\Paket1;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'tasks';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'category',
        'status',
        'due_date',
    ];

    /**
     * Nilai default untuk atribut model.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'category' => 'Kuliah',
        'status' => 'pending',
    ];

    /**
     * Definisi type casting atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_date' => 'string',
        ];
    }
}
