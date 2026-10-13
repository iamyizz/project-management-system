<?php

namespace App\Http\Controllers;

use App\Models\EndUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EndUserController extends Controller
{
    public function index(): View
    {
        $endUsers = EndUser::latest()->get();

        return view('admin.end-users.index', compact('endUsers'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate($this->rules());

        EndUser::create($validated);

        return back()->with(
            'success',
            'End User berhasil ditambahkan'
        );
    }

    public function update(Request $request, EndUser $endUser)
    {

        $validated = $request->validate($this->rules());

        $endUser->update($validated);

        return back()->with(
            'success',
            'End User berhasil diperbarui'
        );

    }

    public function destroy(EndUser $endUser): RedirectResponse
    {
        if ($endUser->projects()->exists()) {
            return back()->with('error', 'End User tidak bisa dihapus karena masih memiliki project. Pindahkan atau hapus project terkait terlebih dahulu.');
        }

        $endUser->delete();

        return back()->with(
            'success',
            'End User berhasil dihapus'
        );
    }

    private function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'industri' => ['nullable', 'string', 'max:255'],
            'contact' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'kota' => ['nullable', 'string', 'max:255'],
            'npwp' => ['nullable', 'string', 'max:32'],
        ];
    }
}
