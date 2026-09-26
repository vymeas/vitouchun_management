<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Check if the authenticated user has the required permission.
     * Usage: ->middleware('permission:students.view')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->can($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'អ្នកមិនមានសិទ្ធិចូលប្រើទិន្នន័យនេះទេ។'], 403);
            }

            abort(403, 'អ្នកមិនមានសិទ្ធិចូលប្រើទិន្នន័យនេះទេ។');
        }

        return $next($request);
    }
}
