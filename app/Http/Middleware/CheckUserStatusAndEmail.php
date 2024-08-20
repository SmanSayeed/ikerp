<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ResponseHelper;

class CheckUserStatusAndEmail
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Check if the user status is active
            if (!$user->status) {
                return ResponseHelper::error('Your account is deactivated.', 403);
            }

            // Check if the email is verified
            if (!$user->email_verified_at) {
                return ResponseHelper::error('Please verify your email address.', 403);
            }
        }

        return $next($request);
    }
}
