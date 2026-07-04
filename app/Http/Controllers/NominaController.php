<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Models\Empleado;
use App\Models\Notificacion;
use App\Models\Nomina;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NominaController extends Controller
{
    private const ESTADOS = ['BORRADOR', 'PENDIENTE', 'APROBADA', 'PAGADA'];

    public function index(Request $request)
    {
        $filters = $request->only(['mes', 'buscar', 'id_departamento', 'estado']);
        $query = Nomina::with('empleado.departamento')->latest('fecha_generacion');

        $this->applyFilters($query, $filters);

        $nominas = $query->paginate(10)->withQueryString();
        $statsQuery = Nomina::query();
        $this->applyFilters($statsQuery, $filters);

        $monthlyRows = (clone $statsQuery)
            ->selectRaw("DATE_FORMAT(periodo_inicio, '%Y-%m') as periodo, SUM(total_pagar) as total")
            ->groupBy('periodo')
            ->orderBy('periodo')
            ->get();

        $statusRows = (clone $statsQuery)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        return view('modules.nomina.index', [
            'nominas' => $nominas,
            'empleados' => Empleado::with('departamento')->where('estado', 'ACTIVO')->orderBy('apellidos')->get(),
            'departamentos' => Departamento::orderBy('nombre_departamento')->get(),
            'estados' => self::ESTADOS,
            'filters' => $filters,
            'totalNomina' => (clone $statsQuery)->sum('total_pagar'),
            'pendientes' => (clone $statsQuery)->where('estado', 'PENDIENTE')->count(),
            'pagadas' => (clone $statsQuery)->where('estado', 'PAGADA')->count(),
            'monthlyLabels' => $monthlyRows->pluck('periodo')->values(),
            'monthlyTotals' => $monthlyRows->pluck('total')->map(fn ($value) => (float) $value)->values(),
            'statusLabels' => collect(self::ESTADOS)->values(),
            'statusTotals' => collect(self::ESTADOS)->map(fn ($estado) => (int) ($statusRows[$estado] ?? 0))->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_empleado' => ['required', 'exists:tbl_empleados,id_empleado'],
            'mes' => ['required', 'date_format:Y-m'],
            'horas_extra' => ['nullable', 'numeric', 'min:0'],
            'monto_horas_extra' => ['nullable', 'numeric', 'min:0'],
            'descuentos' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['required', 'in:'.implode(',', self::ESTADOS)],
        ]);

        $empleado = Empleado::findOrFail($data['id_empleado']);
        $periodoInicio = $data['mes'].'-01';
        $periodoFin = Carbon::createFromFormat('Y-m-d', $periodoInicio)->endOfMonth()->toDateString();
        $salario = (float) $empleado->salario_base;
        $montoHorasExtra = (float) ($data['monto_horas_extra'] ?? 0);
        $descuentos = (float) ($data['descuentos'] ?? 0);
        $total = max(0, $salario + $montoHorasExtra - $descuentos);

        $nomina = Nomina::updateOrCreate(
            [
                'id_empleado' => $empleado->id_empleado,
                'periodo_inicio' => $periodoInicio,
                'periodo_fin' => $periodoFin,
            ],
            [
                'salario_base' => $salario,
                'horas_extra' => (float) ($data['horas_extra'] ?? 0),
                'monto_horas_extra' => $montoHorasExtra,
                'descuentos' => $descuentos,
                'total_pagar' => $total,
                'estado' => $data['estado'],
                'fecha_generacion' => now(),
            ]
        );

        $this->notifyEmployee(
            $empleado,
            'Nomina generada',
            'Se genero su nomina del periodo '.$periodoInicio.' al '.$periodoFin.'. Total: $'.number_format($total, 2).'. Estado: '.$data['estado'].'.'
        );

        return back()->with('status', 'Nomina generada correctamente.');
    }

    public function updateEstado(Request $request, Nomina $nomina)
    {
        $data = $request->validate([
            'estado' => ['required', 'in:'.implode(',', self::ESTADOS)],
        ]);

        $previousState = $nomina->estado;
        $nomina->update(['estado' => $data['estado']]);

        if ($previousState !== $data['estado']) {
            $this->notifyEmployee(
                $nomina->empleado,
                'Estado de nomina actualizado',
                'Su nomina del periodo '.$nomina->periodo_inicio?->format('Y-m').' cambio de '.$previousState.' a '.$data['estado'].'.'
            );
        }

        return back()->with('status', 'Estado de nomina actualizado.');
    }

    private function applyFilters($query, array $filters): void
    {
        if (! empty($filters['mes'])) {
            $query->whereYear('periodo_inicio', substr($filters['mes'], 0, 4))
                ->whereMonth('periodo_inicio', substr($filters['mes'], 5, 2));
        }

        if (! empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }

        if (! empty($filters['buscar'])) {
            $search = $filters['buscar'];
            $query->whereHas('empleado', function ($empleadoQuery) use ($search) {
                $empleadoQuery->where('nombres', 'like', '%'.$search.'%')
                    ->orWhere('apellidos', 'like', '%'.$search.'%')
                    ->orWhere('cedula', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['id_departamento'])) {
            $query->whereHas('empleado', fn ($empleadoQuery) => $empleadoQuery->where('id_departamento', $filters['id_departamento']));
        }
    }

    private function notifyEmployee(?Empleado $empleado, string $title, string $message): void
    {
        if (! $empleado?->id_usuario) {
            return;
        }

        Notificacion::create([
            'id_usuario' => $empleado->id_usuario,
            'titulo' => $title,
            'mensaje' => $message,
            'tipo' => 'NOMINA',
            'leida' => false,
            'fecha_envio' => now(),
        ]);
    }
}
