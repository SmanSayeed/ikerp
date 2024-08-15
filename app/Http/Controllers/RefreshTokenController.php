<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use Illuminate\Http\Request;

class RefreshTokenController extends Controller
{
    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete(); // Delete all existing tokens
        $token = $user->createToken('auth_token')->plainTextToken;

        return ResponseHelper::success(['token' => $token], 'Token refreshed successfully.');
    }
}
