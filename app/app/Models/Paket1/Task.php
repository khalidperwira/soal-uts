<?php

namespace App\Models\Paket1;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'user_id',
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

    /**
     * Pemilik (siswa/user) dari data Task ini.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
