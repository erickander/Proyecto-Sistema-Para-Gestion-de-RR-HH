<?php

namespace Database\Seeders;

use App\Models\Departamento;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre_rol' => 'ADMINISTRADOR', 'descripcion' => 'Control total del sistema'],
            ['nombre_rol' => 'RRHH', 'descripcion' => 'Gestion de recursos humanos'],
            ['nombre_rol' => 'EMPLEADO', 'descripcion' => 'Usuario final del sistema'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['nombre_rol' => $role['nombre_rol']], $role);
        }

        foreach (['Talento Humano', 'Tecnologia', 'Finanzas', 'Operaciones'] as $departamento) {
            Departamento::firstOrCreate(['nombre_departamento' => $departamento]);
        }

        User::firstOrCreate(
            ['nombre_usuario' => 'admin'],
            [
                'id_rol' => Role::where('nombre_rol', 'ADMINISTRADOR')->value('id_rol'),
                'password_hash' => Hash::make('123456'),
                'estado' => 'ACTIVO',
            ]
        );
    }
}
