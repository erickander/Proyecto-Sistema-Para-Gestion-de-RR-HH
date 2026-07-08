<?php

namespace Tests\Feature;

use App\Models\Candidato;
use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Nomina;
use App\Models\Notificacion;
use App\Models\Postulacion;
use App\Models\Role;
use App\Models\TestPregunta;
use App\Models\TestVacante;
use App\Models\User;
use App\Models\Vacante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class RrhhWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_application_redirects_to_test_grades_answers_and_does_not_analyze_ai_automatically(): void
    {
        Storage::fake('public');

        $vacante = $this->createVacanteWithTest();

        $response = $this->post(route('portal.postular', $vacante), [
            'nombres' => 'Ana',
            'apellidos' => 'Molina',
            'cedula' => '0922334455',
            'correo' => 'ana.molina@example.com',
            'telefono' => '0991112222',
            'direccion' => 'Av. Principal 123',
            'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            'consentimiento_ia' => '1',
        ]);

        $postulacion = Postulacion::with('vacante.testActivo.preguntas')->firstOrFail();

        $response->assertRedirect(route('portal.test.show', [
            'postulacion' => $postulacion,
            'token' => $postulacion->token_test,
        ]));

        $preguntas = $postulacion->vacante->testActivo->preguntas;

        $testResponse = $this->post(route('portal.test.submit', [
            'postulacion' => $postulacion,
            'token' => $postulacion->token_test,
        ]), [
            'respuestas' => [
                $preguntas[0]->id_pregunta => 'Laravel',
                $preguntas[1]->id_pregunta => 'Improvisar sin validar',
            ],
        ]);

        $testResponse->assertRedirect(route('portal.gracias'));
        $testResponse->assertSessionHas('status', fn (string $message) => Str::contains($message, '50/100'));

        $this->assertDatabaseHas('tbl_test_respuestas', [
            'id_postulacion' => $postulacion->id_postulacion,
            'id_pregunta' => $preguntas[0]->id_pregunta,
            'es_correcta' => true,
            'puntaje_test' => 50,
        ]);

        $this->assertDatabaseHas('tbl_test_respuestas', [
            'id_postulacion' => $postulacion->id_postulacion,
            'id_pregunta' => $preguntas[1]->id_pregunta,
            'es_correcta' => false,
            'puntaje_test' => 0,
        ]);

        $this->assertDatabaseCount('tbl_analisis_ia', 0);
    }

    public function test_public_application_duplicate_for_same_vacancy_returns_validation_error(): void
    {
        Storage::fake('public');

        $vacante = $this->createVacante();
        $candidato = Candidato::create([
            'nombres' => 'Carlos',
            'apellidos' => 'Perez',
            'cedula' => '0911112222',
            'correo' => 'carlos@example.com',
            'fecha_registro' => now(),
            'estado' => 'ACTIVO',
        ]);

        Postulacion::create([
            'id_candidato' => $candidato->id_candidato,
            'id_vacante' => $vacante->id_vacante,
            'fecha_postulacion' => now(),
            'estado' => 'RECIBIDA',
            'token_test' => Str::random(48),
        ]);

        $response = $this
            ->from(route('portal.vacantes.show', $vacante))
            ->post(route('portal.postular', $vacante), [
                'nombres' => 'Carlos',
                'apellidos' => 'Perez',
                'cedula' => '0911112222',
                'correo' => 'carlos@example.com',
                'telefono' => '0992223333',
                'direccion' => 'Calle Secundaria 456',
                'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
                'consentimiento_ia' => '1',
            ]);

        $response->assertRedirect(route('portal.vacantes.show', $vacante));
        $response->assertSessionHasErrors('correo');
        $this->assertDatabaseCount('tbl_postulaciones', 1);
    }

    public function test_login_redirects_active_users_by_role_and_rejects_inactive_users(): void
    {
        $admin = $this->createUser('ADMINISTRADOR', 'qa_admin');
        $inactive = $this->createUser('RRHH', 'qa_inactive', 'INACTIVO');

        $this->post('/login', [
            'usuario' => $admin->nombre_usuario,
            'password' => 'secret123',
        ])->assertRedirect(route('dashboard.admin'));

        auth()->logout();

        $this->from('/login')->post('/login', [
            'usuario' => $inactive->nombre_usuario,
            'password' => 'secret123',
        ])->assertRedirect('/login')
            ->assertSessionHas('error', 'Usuario o contrasena incorrectos');
    }

    public function test_rrhh_sends_notifications_to_employees_and_employee_marks_them_as_read(): void
    {
        $rrhh = $this->createUser('RRHH', 'qa_rrhh');
        $employeeUser = $this->createUser('EMPLEADO', 'qa_employee');

        $this->actingAs($rrhh)->post(route('notificaciones.store'), [
            'destino' => 'rol',
            'rol_destino' => 'EMPLEADO',
            'titulo' => 'Actualizacion de politica',
            'mensaje' => 'Revise la nueva politica interna.',
            'tipo' => 'RRHH',
        ])->assertSessionHas('status');

        $notification = Notificacion::where('id_usuario', $employeeUser->id_usuario)->firstOrFail();

        $this->assertFalse($notification->leida);

        $this->actingAs($employeeUser)->patch(route('notificaciones.read', $notification))
            ->assertSessionHas('status', 'Notificacion marcada como leida.');

        $this->assertTrue($notification->fresh()->leida);
    }

    public function test_reports_export_filters_payroll_by_quarter_department_and_search(): void
    {
        $rrhh = $this->createUser('RRHH', 'qa_rrhh_report');
        $department = Departamento::create([
            'nombre_departamento' => 'Tecnologia',
            'descripcion' => 'Equipo de pruebas',
        ]);
        $otherDepartment = Departamento::create([
            'nombre_departamento' => 'Finanzas',
            'descripcion' => 'Otro equipo',
        ]);

        $employee = $this->createEmployee($department, 'Lucia', 'Andrade');
        $otherEmployee = $this->createEmployee($otherDepartment, 'Mario', 'Silva');

        Nomina::create([
            'id_empleado' => $employee->id_empleado,
            'periodo_inicio' => '2026-04-01',
            'periodo_fin' => '2026-04-30',
            'salario_base' => 1500,
            'horas_extra' => 4,
            'monto_horas_extra' => 80,
            'descuentos' => 20,
            'total_pagar' => 1560,
            'estado' => 'PAGADA',
            'fecha_generacion' => now(),
        ]);

        Nomina::create([
            'id_empleado' => $otherEmployee->id_empleado,
            'periodo_inicio' => '2026-05-01',
            'periodo_fin' => '2026-05-31',
            'salario_base' => 1200,
            'horas_extra' => 0,
            'monto_horas_extra' => 0,
            'descuentos' => 0,
            'total_pagar' => 1200,
            'estado' => 'PAGADA',
            'fecha_generacion' => now(),
        ]);

        $response = $this->actingAs($rrhh)->get(route('reportes.export', [
            'modulo' => 'nomina',
            'periodo' => 'trimestral',
            'anio' => 2026,
            'trimestre' => 2,
            'id_departamento' => $department->id_departamento,
            'buscar' => 'Lucia',
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('Lucia Andrade', $csv);
        $this->assertStringNotContainsString('Mario Silva', $csv);
    }

    private function createVacante(): Vacante
    {
        return Vacante::create([
            'titulo' => 'Desarrollador Laravel',
            'descripcion' => 'Construccion y mantenimiento de modulos internos.',
            'requisitos' => 'Laravel, SQL, pruebas automatizadas',
            'estado' => 'ABIERTA',
            'fecha_publicacion' => now(),
        ]);
    }

    private function createVacanteWithTest(): Vacante
    {
        $vacante = $this->createVacante();
        $test = TestVacante::create([
            'id_vacante' => $vacante->id_vacante,
            'titulo' => 'Test tecnico Laravel',
            'descripcion' => 'Preguntas de seleccion multiple',
            'estado' => 'ACTIVO',
        ]);

        TestPregunta::create([
            'id_test' => $test->id_test,
            'pregunta' => 'Que framework PHP se usa para este puesto?',
            'tipo' => 'MULTIPLE',
            'opciones' => ['Laravel', 'Django', 'Rails'],
            'respuesta_correcta' => 'Laravel',
            'puntaje_maximo' => 50,
            'orden' => 1,
        ]);

        TestPregunta::create([
            'id_test' => $test->id_test,
            'pregunta' => 'Que accion fortalece una entrega critica?',
            'tipo' => 'MULTIPLE',
            'opciones' => ['Improvisar sin validar', 'Validar con pruebas', 'Ignorar errores'],
            'respuesta_correcta' => 'Validar con pruebas',
            'puntaje_maximo' => 50,
            'orden' => 2,
        ]);

        return $vacante;
    }

    private function createUser(string $roleName, string $username, string $status = 'ACTIVO'): User
    {
        $role = Role::firstOrCreate(
            ['nombre_rol' => $roleName],
            ['descripcion' => $roleName]
        );

        return User::create([
            'id_rol' => $role->id_rol,
            'nombre_usuario' => $username,
            'correo' => $username.'@example.com',
            'password_hash' => Hash::make('secret123'),
            'estado' => $status,
        ]);
    }

    private function createEmployee(Departamento $department, string $firstName, string $lastName): Empleado
    {
        $username = Str::slug($firstName.'.'.$lastName);
        $user = $this->createUser('EMPLEADO', $username);

        return Empleado::create([
            'id_usuario' => $user->id_usuario,
            'id_departamento' => $department->id_departamento,
            'cedula' => (string) random_int(1000000000, 1999999999),
            'nombres' => $firstName,
            'apellidos' => $lastName,
            'correo' => $username.'@example.com',
            'telefono' => '0999999999',
            'direccion' => 'Direccion de prueba',
            'cargo' => 'Analista',
            'salario_base' => 1500,
            'fecha_ingreso' => '2026-04-01',
            'estado' => 'ACTIVO',
        ]);
    }
}
