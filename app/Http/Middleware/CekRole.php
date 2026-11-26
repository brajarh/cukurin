<?php

namespace App\Http\Middleware;

use App\Models\UserRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CekRole
{
    public function handle(
        Request $request,
        Closure $next,
        ...$roles
    ): Response
    {
        // Pastikan pengguna sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Mengisi session jika belum tersedia
        if (!$request->session()->has('user_role')) {

            $ur = UserRole::with('role')
                ->where('user_id', Auth::id())
                ->first();

            session([
                'user_role' => $ur?->role_id,
                'user_role_name' => $ur?->role?->nama_role,
            ]);
        }

        // Mengambil role dari session
        $role = (string) session('user_role');
        $namaRole = (string) session('user_role_name');

        // Memeriksa hak akses
        if (
            in_array($role, $roles, true) ||
            in_array($namaRole, $roles, true)
        ) {
            return $next($request);
        }

        abort(403, 'Akses ditolak.');
    }
}