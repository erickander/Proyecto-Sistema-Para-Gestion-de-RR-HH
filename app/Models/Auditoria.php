<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auditoria extends Model
{
    protected $table = 'tbl_auditoria';

    protected $primaryKey = 'id_log';

    public $timestamps = false;

    protected $fillable = [
        'id_usuario',
        'accion',
        'modulo',
        'detalle',
        'ip_address',
        'nivel',
        'fecha_evento',
    ];

    protected function casts(): array
    {
        return [
            'fecha_evento' => 'datetime',
        ];
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id_usuario');
    }
}
