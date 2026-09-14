<?php

namespace App\Models\Paket4;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'tickets';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_number',
        'customer_name',
        'service_type',
        'status',
        'counter_number',
        'notes',
    ];

    /**
     * Nilai default untuk atribut model.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'service_type' => 'Customer Service',
        'status' => 'Menunggu',
    ];
}
