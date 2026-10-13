@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-xl border-slate-200 bg-white text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:bg-slate-50']) }}>
