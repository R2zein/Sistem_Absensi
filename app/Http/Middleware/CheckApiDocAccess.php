<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;

class CheckApiDocAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            abort(403, 'Akses Tidak Dikenali');
        }

        $user = auth()->user();
        if (Auth::user()->hasRole('super_admin')) {
            return $next($request);
        }

        abort(403, 'Kamu Tidak Memiliki Akses Ke Halaman Ini');
}
}