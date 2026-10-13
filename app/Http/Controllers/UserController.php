<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->get();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validateWithBag('create', [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'role' => 'required|in:admin,user',
        ]);

        User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan');
    }

    public function update(Request $request, User $user)
    {
        $request->validateWithBag('edit', [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role' => 'required|in:admin,user',
            'is_active' => 'required|boolean',
        ]);

        if ($user->isLastActiveAdministrator()
            && ($request->role !== 'admin' || ! $request->boolean('is_active'))) {
            return back()
                ->withErrors(['role' => 'Minimal harus ada satu administrator aktif.'], 'edit')
                ->withInput();
        }

        $data = $request->only('nama', 'email', 'role', 'is_active');

        // Biar admin nggak nggak sengaja nonaktifin / nurunin role akunnya sendiri
        if ($user->is(auth()->user())) {
            $data['role'] = 'admin';
            $data['is_active'] = true;
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui');
    }

    public function sendPasswordResetLink(User $user): RedirectResponse
    {
        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            return redirect()
                ->route('admin.users.index')
                ->with('success', "Link reset password dikirim ke {$user->email}.");
        }

        $message = $status === Password::RESET_THROTTLED
            ? 'Link reset baru saja dikirim. Coba lagi beberapa saat.'
            : 'Link reset password gagal dikirim. Coba lagi atau periksa konfigurasi email.';

        return redirect()
            ->route('admin.users.index')
            ->with('error', $message);
    }
}
