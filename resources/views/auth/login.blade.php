<x-layouts.guest>
    <x-slot:title>登入</x-slot:title>
    <x-slot:heading>請登入以繼續使用系統</x-slot:heading>

    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="username" class="mb-1.5 block text-sm font-medium text-slate-700">帳號</label>
            <input
                id="username"
                type="text"
                name="username"
                value="{{ old('username') }}"
                required
                autofocus
                autocomplete="username"
                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                placeholder="請輸入帳號"
            >
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">密碼</label>
            <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                placeholder="請輸入密碼"
            >
        </div>

        <div class="flex items-center gap-2">
            <input
                id="remember"
                type="checkbox"
                name="remember"
                value="1"
                class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500"
                @checked(old('remember'))
            >
            <label for="remember" class="text-sm text-slate-600">記住我</label>
        </div>

        <button
            type="submit"
            class="flex w-full items-center justify-center rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2"
        >
            登入
        </button>
    </form>
</x-layouts.guest>
