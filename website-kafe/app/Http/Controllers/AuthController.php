<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    public function tampilGantiPassword()
    {
        return view('auth.ganti-password');
    }

    public function prosesGantiPassword(Request $permintaan)
    {
        $data = $permintaan->validate([
            'username' => ['required', 'string'],
            'password_lama' => ['required', 'string'],
            'password_baru' => ['required', 'string', 'min:8', 'confirmed', 'different:password_lama'],
        ], [
            'password_baru.min' => 'Password baru minimal 8 karakter.',
            'password_baru.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password_baru.different' => 'Password baru tidak boleh sama dengan password lama.',
        ]);

        $pengguna = User::where('username', $data['username'])
            ->whereIn('role', ['Manajer', 'Kasir'])
            ->first();

        if (! $pengguna || ! Hash::check($data['password_lama'], $pengguna->password)) {
            return back()
                ->withErrors(['username' => 'Username atau password lama tidak cocok.'])
                ->onlyInput('username');
        }

        $pengguna->update([
            'password' => Hash::make($data['password_baru']),
        ]);

        // Jika staf sedang dalam kondisi login, logout dulu agar login ulang pakai password baru
        if (Auth::check()) {
            Auth::logout();
            $permintaan->session()->invalidate();
            $permintaan->session()->regenerateToken();
        }

        return redirect()
            ->route('login')
            ->with('sukses', 'Password berhasil diganti. Silakan login menggunakan password baru.');
    }
}