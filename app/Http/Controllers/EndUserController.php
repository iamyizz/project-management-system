<?php

namespace App\Http\Controllers;

use App\Models\EndUser;
use Illuminate\Http\Request;

class EndUserController extends Controller
{

    public function index()
    {
        $endUsers = EndUser::latest()->get();

        return view('admin.end-users.index', compact('endUsers'));
    }


    public function store(Request $request)
    {

        $validated = $request->validate([
            'nama' => 'required',
            'industri' => 'nullable',
            'contact' => 'nullable',
            'telepon' => 'nullable',
            'email' => 'nullable|email',
            'kota' => 'nullable',
            'npwp' => 'nullable',
        ]);


        EndUser::create($validated);


        return back()->with(
            'success',
            'End User berhasil ditambahkan'
        );
    }


    public function update(Request $request, EndUser $endUser)
    {

        $validated = $request->validate([
            'nama' => 'required',
            'industri' => 'nullable',
            'contact' => 'nullable',
            'telepon' => 'nullable',
            'email' => 'nullable|email',
            'kota' => 'nullable',
            'npwp' => 'nullable',
        ]);


        $endUser->update($validated);


        return back()->with(
            'success',
            'End User berhasil diperbarui'
        );

    }


    public function destroy(EndUser $endUser)
    {
        $endUser->delete();

        return back()->with(
            'success',
            'End User berhasil dihapus'
        );
    }

}
