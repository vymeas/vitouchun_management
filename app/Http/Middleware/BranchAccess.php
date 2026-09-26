<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BranchAccess
{
    /**
     * Enforce branch isolation.
     * Super Admins bypass this — all others must have a valid branch_id.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Super admin can access everything
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Regular users must be active and have a branch
        if ($user->status !== 'active' || $user->is_deleted) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['username' => 'គណនីរបស់អ្នកត្រូវបានបិទ។']);
        }

        if (!$user->branch_id && Branch::query()->exists()) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['username' => 'គណនីរបស់អ្នកមិនត្រូវបានភ្ជាប់ជាមួយសាខាណាមួយទេ។ សូមទាក់ទង Admin។']);
        }

        return $next($request);
    }
}
