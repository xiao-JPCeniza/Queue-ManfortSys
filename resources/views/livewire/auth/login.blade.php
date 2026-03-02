<div class="w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-emerald-800 px-8 py-6 text-center">
            <h1 class="text-2xl font-bold text-white">LGU Queue System</h1>
            <p class="text-emerald-200 text-sm mt-1">Municipality of Manolo Fortich</p>
        </div>
        <div class="p-8">
            <h2 class="text-lg font-semibold text-slate-800 mb-6">Sign in to your account</h2>
            <form wire:submit="login" class="space-y-5">
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" id="email" wire:model="email"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('email') border-red-500 @enderror"
                           placeholder="admin@manolofortich.gov.ph" autofocus>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" id="password" wire:model="password"
                           class="w-full rounded-lg border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 @error('password') border-red-500 @enderror"
                           placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-center">
                    <input type="checkbox" id="remember" wire:model="remember"
                           class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                    <label for="remember" class="ml-2 text-sm text-slate-600">Remember me</label>
                </div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2.5 rounded-lg transition">
                    Sign in
                </button>
            </form>
        </div>
    </div>
    <p class="text-center text-slate-500 text-sm mt-4">
        Queue System for LGU Manolo Fortich &copy; {{ date('Y') }}
    </p>
</div>
