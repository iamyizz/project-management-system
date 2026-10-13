<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center rounded-xl border border-transparent bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition duration-150 hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-500/20 active:bg-indigo-800 disabled:cursor-not-allowed disabled:opacity-60']) }}>
    {{ $slot }}
</button>
