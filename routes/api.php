<?php

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\RefreshTokenController;
use App\Http\Controllers\Admin\AdminManagesUserController;
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
Route::post('login', [AuthController::class, 'login'])->middleware('check.user.status.email');
;

Route::post('refresh-token', [RefreshTokenController::class, 'refresh'])->middleware('auth:sanctum');
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Route::get('/admin/profile', function () {
    //     return ResponseHelper::success(null, 'You are an admin.');
    // });
    Route::get('/user/profile', [AdminController::class, 'getProfile']);
    Route::put('/user/profile', [AdminController::class, 'updateProfile']);

    Route::put('users/{user}/email-verification', [AdminManagesUserController::class, 'updateEmailVerification']);
    Route::put('users/{user}/status', [AdminManagesUserController::class, 'updateStatus']);
    Route::delete('users/{user}/soft-delete', [AdminManagesUserController::class, 'softDeleteUser']);
    Route::delete('users/{user}/hard-delete', [AdminManagesUserController::class, 'hardDeleteUser']);
    Route::put('users/{user}/password', [AdminManagesUserController::class, 'updateUserPassword']);

    Route::get('users/profile/{id}', [AdminManagesUserController::class, 'getUserById']);

    Route::get('users/soft-deleted/{id}', [AdminManagesUserController::class, 'getUserById']);
    Route::get('users/soft-deleted', [AdminManagesUserController::class, 'getAllSoftDeletedUsers']);
    Route::put('users/{id}/restore', [AdminManagesUserController::class, 'restoreUser']);
    Route::put('users/{user}', [AdminManagesUserController::class, 'updateUserInfo']);
    Route::get('/users', [AdminManagesUserController::class, 'usersList']);
});

Route::middleware(['auth:sanctum', 'role:client'])->group(function () {
    Route::get('/client/profile', [ClientController::class, 'getClientProfile']);
    Route::put('/client/profile', [ClientController::class, 'updateClientProfile']);
});



Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/verify-email/{user}', [AuthController::class, 'verifyEmail'])->name('verify.email');
// In routes/api.php
Route::post('/resend-verification-email', [AuthController::class, 'resendVerificationEmail']);
/** Reset password */
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password-by-email', [AuthController::class, 'resetPasswordByEmail']);

