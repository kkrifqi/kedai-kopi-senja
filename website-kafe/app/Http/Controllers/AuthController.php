<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function tampilLogin()
    {
        if (Auth::check()) {
            return $this->arahkanSesuaiPeran(Auth::user());
        }

        return view('auth.login');
    }

    public function prosesLogin(Request $permintaan)
    {
        $kredensial = $permintaan->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($kredensial, $permintaan->boolean('ingat_saya'))) {
            return back()
                ->withErrors(['username' => 'Username atau password salah.'])
                ->onlyInput('username');
        }

        $permintaan->session()->regenerate();

        return $this->arahkanSesuaiPeran(Auth::user());
    }

    public function logout(Request $permintaan)
    {
        Auth::logout();
        $permintaan->session()->invalidate();
        $permintaan->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function arahkanSesuaiPeran($pengguna)
    {
        if ($pengguna->role === 'Manajer') {
            return redirect('/admin');
        }

        if ($pengguna->role === 'Kasir') {
            return redirect()->route('kasir.dashboard');
        }

        Auth::logout();

        return redirect()->route('login')->withErrors([
            'username' => 'Peran akun tidak diizinkan mengakses web.',
        ]);
    }
}