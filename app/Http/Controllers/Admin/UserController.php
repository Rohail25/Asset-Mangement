<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        return view('admin.login');
    }

    /**
     * Handle a login attempt.
     */

    public function login(Request $request)
    {
        $data = $request->validate([
            'email'     => ['required', 'email'],
            'password'  => ['required', 'string', 'min:8'],
        ]);

        $remember = $request->boolean('remember');

        $email = $data['email'];
        $pwd   = $data['password'];

        $auditorExists = Auditor::where('email', $email)->exists();
        $userExists    = User::where('email', $email)->exists();

        // Prefer the table that actually contains the email.
        $guardsToTry = $auditorExists ? ['auditor', 'web'] : ($userExists ? ['web', 'auditor'] : ['web', 'auditor']);

        foreach ($guardsToTry as $guard) {
            if (Auth::guard($guard)->attempt(['email' => $email, 'password' => $pwd], $remember)) {
                $request->session()->regenerate();

                if ($guard === 'web') {
                    $user = Auth::guard('web')->user();

                    if (($user->role ?? null) !== 'admin') {
                        Log::warning('User tried to log in via admin but is not admin: ' . $email);
                        Auth::guard('web')->logout();
                        continue;
                    }

                    Log::info('Admin logged in successfully: ' . $email);
                    return redirect()->route('admin.client.index')->with('ok', 'Welcome back, Admin ' . $user->name . '!');
                }


                // auditor
                $auditor = Auth::guard('auditor')->user();
                // dd($auditor);
                if (($auditor->role ?? null) !== 'auditor') {
                    Auth::guard('auditor')->logout();
                    continue;
                }
                if (isset($auditor->status) && $auditor->status !== 'active') {
                    Auth::guard('auditor')->logout();
                    return back()->withErrors(['email' => 'Your auditor account is not active.'])->onlyInput('email');
                }
                return redirect()->route('auditor.client.index')->with('ok', 'Welcome back, Auditor ' . $auditor->name . '!');
            }
        }

        return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
    }


    /**
     * Logout the admin.
     */
    public function logout(Request $request)
    {
        foreach (['web', 'auditor'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('ok', 'You have been logged out.');
    }
}
