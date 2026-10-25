<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Peran
{
    public function handle(Request $request, Closure $next, string ...$peran)
    {
        if (! in_array($request->user()?->role, $peran, true)) {
            return redirect('/'); // '/' mengarahkan sesuai peran, jadi tidak terjadi loop
        }
        return $next($request);
    }
}
