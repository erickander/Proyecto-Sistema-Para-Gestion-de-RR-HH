<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Departamento;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SistemaController extends Controller
{
    public function index()
    {
        return view('modules.sistema.index', [
            'roles' => Role::orderBy('nombre_rol')->get(),
            'departamentos' => Departamento::orderBy('nombre_departamento')->get(),
        ]);
    }

    public function storeDepartamento(Request $request)
    {
        $data = $request->validate([
            'nombre_departamento' => ['required', 'string', 'max:100', 'unique:tbl_departamentos,nombre_departamento'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ]);

        Departamento::create($data);

        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => 'Sistema',
            'accion' => 'CREAR_DEPARTAMENTO',
            'detalle' => 'Departamento creado: '.$data['nombre_departamento'],
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);

        return back()->with('status', 'Departamento creado correctamente.');
    }

    public function backup(Request $request)
    {
        $database = config('database.connections.mysql.database');
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->filter(fn ($table) => str_starts_with($table, 'tbl_'))
            ->values();

        $sql = "-- Respaldo {$database}\n-- Generado: ".now()->format('Y-m-d H:i:s')."\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $create = DB::select("SHOW CREATE TABLE `{$table}`")[0]->{'Create Table'};
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n{$create};\n\n";

            $rows = DB::table($table)->get();

            foreach ($rows as $row) {
                $data = (array) $row;
                $columns = collect(array_keys($data))->map(fn ($column) => "`{$column}`")->implode(', ');
                $values = collect(array_values($data))
                    ->map(fn ($value) => is_null($value) ? 'NULL' : DB::getPdo()->quote((string) $value))
                    ->implode(', ');

                $sql .= "INSERT INTO `{$table}` ({$columns}) VALUES ({$values});\n";
            }

            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backups/db_rrhh_'.now()->format('Ymd_His').'.sql';
        Storage::disk('local')->put($filename, $sql);

        Auditoria::create([
            'id_usuario' => auth()->id(),
            'modulo' => 'Sistema',
            'accion' => 'GENERAR_RESPALDO',
            'detalle' => 'Respaldo generado: '.$filename,
            'ip_address' => $request->ip(),
            'nivel' => 'INFO',
            'fecha_evento' => now(),
        ]);

        return response()->download(storage_path('app/private/'.$filename));
    }
}
