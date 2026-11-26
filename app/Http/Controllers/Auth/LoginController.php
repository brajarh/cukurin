<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\UserRole;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Membatasi percobaan login berulang
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if (Auth::attempt($cred, $request->boolean('remember'))) {

            $request->session()->regenerate();

            $this->clearLoginAttempts($request);

            $ur = UserRole::with('role')
                ->where('user_id', Auth::id())
                ->first();

            session([
                'user_role' => $ur?->role_id,
                'user_role_name' => $ur?->role?->nama_role,
            ]);

            return redirect()->intended('/home')
                ->with('success', 'Login berhasil!');
        }

        $this->incrementLoginAttempts($request);

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Login gagal. Email atau password salah.'
            ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login')
            ->with('success', 'Anda telah keluar.');
    }
}