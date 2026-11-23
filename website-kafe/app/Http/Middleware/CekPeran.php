<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekPeran
{
    public function handle(Request $request, Closure $next, string $peran): Response
    {
        $pengguna = $request->user();

        if (! $pengguna) {
            return redirect()->route('login');
        }

        $rolePengguna = strtolower(trim((string) ($pengguna->role instanceof \BackedEnum
            ? $pengguna->role->value
            : $pengguna->role)));

        if ($rolePengguna !== strtolower(trim($peran))) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}