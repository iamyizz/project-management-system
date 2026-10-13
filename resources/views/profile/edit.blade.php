<x-admin-layout title="Profil Saya">
    <div class="mx-auto max-w-5xl space-y-7 sm:space-y-8">
        <header>
            <p class="workspace-kicker">Pengaturan akun</p>
            <h2 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 sm:text-3xl">Profil Saya</h2>
            <p class="mt-2 text-sm text-slate-500">Kelola informasi pribadi dan keamanan akun workspace-mu.</p>
        </header>

        <section class="workspace-panel overflow-hidden">
            <div class="flex items-center gap-4 border-b border-slate-100 bg-gradient-to-r from-indigo-50/70 to-white px-5 py-5 sm:px-7">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-600 text-lg font-bold text-white shadow-sm shadow-indigo-600/20">{{ strtoupper(substr($user->nama, 0, 1)) }}</div>
                <div class="min-w-0">
                    <h3 class="truncate font-bold text-slate-900">{{ $user->nama }}</h3>
                    <p class="truncate text-sm text-slate-500">{{ $user->email }} <span class="mx-1 text-slate-300">·</span> {{ ucfirst($user->role) }}</p>
                </div>
            </div>
            <div class="p-5 sm:p-7">
                <div class="max-w-2xl">@include('profile.partials.update-profile-information-form')</div>
            </div>
        </section>

        <section class="workspace-panel p-5 sm:p-7">
            <div class="max-w-2xl">@include('profile.partials.update-password-form')</div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-rose-200 bg-white shadow-sm">
            <div class="border-l-4 border-rose-400 p-5 sm:p-7">
                <div class="max-w-2xl">@include('profile.partials.delete-user-form')</div>
            </div>
        </section>
    </div>
</x-admin-layout>
