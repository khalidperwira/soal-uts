<?php

namespace App\Models\Paket2;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'attendances';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nim',
        'student_name',
        'date',
        'time_in',
        'time_out',
        'status',
        'note',
    ];

    /**
     * Nilai default untuk atribut model.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'Hadir',
    ];

    /**
     * Definisi type casting atribut model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'string',
        ];
    }
}
