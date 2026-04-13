<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckNotepadPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, $role)
    {
        $notepad = $request->route('notepad');

        $userRole = $notepad->userRole(auth()->user());

        if ($userRole === 'owner') {
            return $next($request);
        }

        if ($role === 'edit' && $userRole === 'editor') {
            return $next($request);
        }

        if ($role === 'view' && in_array($userRole, ['viewer', 'editor'])) {
            return $next($request);
        }

        abort(403);
    }
}
