<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'tbl_usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    protected $fillable = [
        'id_rol',
        'nombre_usuario',
        'correo',
        'password_hash',
        'estado',
        'ultimo_acceso',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'id_rol', 'id_rol');
    }

    public function empleado()
    {
        return $this->hasOne(Empleado::class, 'id_usuario', 'id_usuario');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_usuario', 'id_usuario');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'id_usuario', 'id_usuario');
    }

    public function getAuthIdentifierName()
    {
        return 'id_usuario';
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ultimo_acceso' => 'datetime',
        ];
    }
}
