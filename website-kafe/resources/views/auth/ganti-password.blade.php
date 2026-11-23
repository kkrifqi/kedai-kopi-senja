<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password Staf - Kedai Kopi Senja</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-stone-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold text-stone-800 mb-1">Ganti Password</h1>
        <p class="text-sm text-stone-500 mb-6">Masukkan username dan password lama untuk verifikasi</p>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $galat)
                        <li>{{ $galat }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.ganti.proses') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username', auth()->user()->username ?? '') }}" required autofocus
                    class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-600">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Password Lama</label>
                <input type="password" name="password_lama" required
                    class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-600">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Password Baru (min. 8 karakter)</label>
                <input type="password" name="password_baru" required minlength="8"
                    class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-600">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_baru_confirmation" required minlength="8"
                    class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-600">
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-amber-700 hover:bg-amber-800 text-white font-semibold py-2.5 transition">
                Simpan Password Baru
            </button>
        </form>

        <div class="mt-5 text-center">
            <a href="{{ route('login') }}" class="text-sm text-amber-700 hover:underline font-medium">
                ← Kembali ke Halaman Login
            </a>
        </div>
    </div>
</body>
</html>