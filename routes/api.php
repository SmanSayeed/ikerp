<?php

use App\Helpers\ResponseHelper;
use App\Http\Controllers\RefreshTokenController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('refresh-token', [RefreshTokenController::class, 'refresh'])->middleware('auth:sanctum');
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Route::get('/admin/profile', function () {
    //     return ResponseHelper::success(null, 'You are an admin.');
    // });
    Route::get('/admin/profile', [UserController::class, 'getProfile']);
    Route::put('/admin/profile', [UserController::class, 'updateProfile']);
});


Route::get('/verify-email/{user}', [AuthController::class, 'verifyEmail'])->name('verify.email');

// In routes/api.php
Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail']);


