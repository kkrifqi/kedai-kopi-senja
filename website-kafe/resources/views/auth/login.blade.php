<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Staf - Kedai Kopi Senja</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-stone-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold text-stone-800 mb-1">Kedai Kopi Senja</h1>
        <p class="text-sm text-stone-500 mb-6">Masuk sebagai Kasir atau Manajer</p>

        @if (session('sukses'))
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 p-3 text-sm text-emerald-700">
                {{ session('sukses') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.proses') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-600">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Password</label>
                <input type="password" name="password" required
                    class="w-full rounded-lg border border-stone-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-amber-600">
            </div>

            <div class="mt-4 text-right">
                <a href="{{ route('password.ganti') }}" class="text-sm text-amber-700 hover:underline font-medium">
                    Lupa Password?
                </a>
            </div>

            <button type="submit"
                class="w-full rounded-lg bg-amber-700 hover:bg-amber-800 text-white font-semibold py-2.5 transition">
                Masuk
            </button>
        </form>
    </div>
</body>
</html>