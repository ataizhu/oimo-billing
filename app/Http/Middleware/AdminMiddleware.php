<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        Log::info('AdminMiddleware: Checking authentication', [
            'is_authenticated' => Auth::check(),
            'user' => Auth::user(),
            'is_admin' => Auth::check() ? Auth::user()->is_admin : null
        ]);

        if (!Auth::check() || !Auth::user()->is_admin) {
            Log::warning('AdminMiddleware: Access denied', [
                'user_id' => Auth::id(),
                'path' => $request->path()
            ]);
            return redirect()->route('home')->with('error', 'У вас нет прав для доступа к этой странице.');
        }

        return $next($request);
    }
}