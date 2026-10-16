    {{-- ================= MODAL CREATE & EDIT ================= --}}
    @foreach (['create' => __('Tambah proyek'), 'edit' => __('Ubah proyek')] as $mode => $title)
        <div id="{{ $mode }}Modal"
            class="modal fixed inset-0 z-50 {{ $formType === $mode ? 'flex' : 'hidden' }} items-center justify-center bg-slate-900/50 p-4">
            <div class="max-h-screen w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
                    <button type="button" onclick="closeModal('{{ $mode }}Modal')" class="text-2xl leading-none text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <form id="{{ $mode }}Form" method="POST"
                    action="{{ $mode === 'create'
                        ? route('admin.projects.store')
                        : ($formType === 'edit' && old('edit_id') ? route('admin.projects.update', old('edit_id')) : '') }}">
                    @csrf
                    @if ($mode === 'create')
                        <input type="hidden" name="submission_token" value="{{ old('submission_token', (string) \Illuminate\Support\Str::uuid()) }}">
                    @endif
                    @if ($mode === 'edit')
                        @method('PUT')
                        <input type="hidden" name="edit_id" value="{{ $formType === 'edit' ? old('edit_id') : '' }}">
                    @endif
                    <input type="hidden" name="form_type" value="{{ $mode }}">
                    <div class="grid grid-cols-1 gap-4 px-6 py-5 sm:grid-cols-2">
                        @foreach ($fields as $name => $field)
                            @php
                                $type       = $field['type'] ?? 'text';
                                $id         = $mode . '_' . $name;
                                $required   = $field['required'] ?? false;
                                $readonly   = $field['readonly'] ?? false;
                                $value      = $formType === $mode
                                    ? old($name)
                                    : ($mode === 'create' && $name === 'status' ? 'planning' : '');
                            @endphp
                            <div class="{{ !empty($field['span']) ? 'sm:col-span-2' : '' }}">
                                <label for="{{ $id }}" class="{{ $label }}">
                                    {{ $field['label'] }}
                                    @if ($required)<span class="text-red-500">*</span>@endif
                                </label>
                                @if ($type === 'select')
                                    <select id="{{ $id }}" name="{{ $name }}" class="{{ $input }}" @required($required) @if($readonly) disabled @endif>
                                        <option value="">{{ __('Pilih :label', ['label' => $field['label']]) }}</option>
                                        @foreach ($field['options'] as $key => $text)
                                            <option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $text }}</option>
                                        @endforeach
                                    </select>
                                @elseif ($type === 'money' || $type === 'percent')
                                    <div class="relative">
                                        @if ($type === 'money')
                                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-slate-500">Rp</span>
                                        @endif
                                        <input
                                            id="{{ $id }}"
                                            type="number"
                                            name="{{ $name }}"
                                            value="{{ $value }}"
                                            step="0.01"
                                            @if ($name === 'internal_cost') min="0" @endif
                                            @if ($type === 'percent') min="-999.99" max="999.99" @endif
                                            class="{{ $input }} {{ $type === 'money' ? 'pl-10' : 'pr-10' }} {{ $readonly ? 'bg-slate-100 cursor-not-allowed' : '' }}"
                                            @if ($readonly) readonly @endif
                                            data-calculate="{{ in_array($name, ['project_value', 'internal_cost']) ? 'true' : 'false' }}">
                                        @if ($type === 'percent')
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-slate-500">%</span>
                                        @endif
                                    </div>
                                @elseif ($type === 'date')
                                    <input id="{{ $id }}" type="date" name="{{ $name }}" value="{{ $value }}"
                                        class="{{ $input }}" @required($required)>
                                @else
                                    <input id="{{ $id }}" type="text" name="{{ $name }}" value="{{ $value }}" maxlength="255"
                                        class="{{ $input }}" @required($required)>
                                @endif
                                @if ($formType === $mode)
                                    @error($name)
                                        <p class="field-error mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-end gap-2 border-t border-slate-200 px-6 py-4">
                        <button type="button" onclick="closeModal('{{ $mode }}Modal')"
                            class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" data-form-submit
                            class="action-btn action-btn--primary px-4 py-2 text-sm font-semibold">
                            {{ __('Simpan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

