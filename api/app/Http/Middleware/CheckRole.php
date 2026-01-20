<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
public function handle(Request $request, Closure $next, ...$roles): Response
{
    $userRole = null;

    // Cek Session Hardcoded dulu
    if (session('is_hardcoded_admin')) {
        $userRole = session('admin_data.role');
    }
    // Jika tidak ada, cek Database
    elseif (Auth::check()) {
        $userRole = Auth::user()->role;
    }

    // Izinkan jika role cocok
    if ($userRole && in_array($userRole, $roles)) {
        return $next($request);
    }

    // Jika tidak login sama sekali
    if (!$userRole) {
        return redirect()->route('login');
    }

    abort(403, 'Akses Ditolak.');
}
}
