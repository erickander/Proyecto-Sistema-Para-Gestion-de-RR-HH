<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'usuario' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::with('role')
            ->where('nombre_usuario', $credentials['usuario'])
            ->first();

        if (! $user || $user->estado !== 'ACTIVO' || ! Hash::check($credentials['password'], $user->password_hash)) {
            return back()
                ->with('error', 'Usuario o contrasena incorrectos')
                ->onlyInput('usuario');
        }

        Auth::login($user);
        $request->session()->regenerate();

        $user->forceFill(['ultimo_acceso' => now()])->save();

        return redirect()->intended($this->routeByRole($user->role?->nombre_rol));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendRecoveryToken(Request $request)
    {
        $data = $request->validate([
            'correo' => ['required', 'email'],
        ]);

        $token = Str::random(64);

        return back()->with('status', 'Modulo preparado. Token generado: '.$token);
    }

    private function routeByRole(?string $role): string
    {
        return match ($role) {
            'ADMINISTRADOR' => route('dashboard.admin'),
            'RRHH' => route('dashboard.rrhh'),
            'EMPLEADO' => route('dashboard.empleado'),
            default => route('dashboard.rrhh'),
        };
    }
}
