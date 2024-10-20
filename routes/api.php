<?php

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminManagesClientController;
use App\Http\Controllers\Admin\AdminManagesSellerProfileController;
use App\Http\Controllers\ChildClientController;
use App\Http\Controllers\Client\ClientAuthController;
use App\Http\Controllers\Client\ClientController;
use App\Http\Controllers\NodesController;
use App\Http\Controllers\PowerDataController;
use App\Http\Controllers\RefreshTokenController;
use App\Http\Controllers\Admin\AdminManagesUserController;
use App\Http\Controllers\SellerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DeviceController;
use App\Http\Controllers\InvoiceChildClientController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Sqlite\SqliteController;
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


    /*************Admin manages client******** */
    Route::prefix('admin/clients')->group(function () {
        Route::get('/', [AdminManagesClientController::class, 'clientsList']);
        Route::get('/profile', [AdminManagesClientController::class, 'getProfile']);
        // Route::put('/profile', [AdminManagesClientController::class, 'updateProfile']);
        Route::patch('/{client}/email-verification', [AdminManagesClientController::class, 'updateEmailVerification']);
        Route::patch('/{client}/status', [AdminManagesClientController::class, 'updateStatus']);

        Route::put('/{client}', [AdminManagesClientController::class, 'updateClientInfo']);

        Route::get('/{id}', [AdminManagesClientController::class, 'getClientById']);
        Route::post('/restore/{id}', [AdminManagesClientController::class, 'restoreClient']);
        Route::get('/trashed', [AdminManagesClientController::class, 'getAllClientsWithTrashed']);
        Route::delete('/{client}/soft-delete', [AdminManagesClientController::class, 'softDeleteClient']);
        Route::delete('/{client}/hard-delete', [AdminManagesClientController::class, 'hardDeleteClient']);
        Route::patch('/{client}/password', [AdminManagesClientController::class, 'updateClientPassword']);
    });


    Route::prefix('admin/clients/seller')->group(function () {
        Route::get('{clientId}', [AdminManagesSellerProfileController::class, 'show']);
        Route::put('{clientId}', [AdminManagesSellerProfileController::class, 'update']);
    });

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


Route::prefix('admin')->group(function () {
    Route::middleware('auth:admin-api')->group(function () {
        Route::get('devices', [DeviceController::class, 'index']); // List all devices
        Route::post('devices', [DeviceController::class, 'store']); // Create a new device
        Route::get('devices/{id}', [DeviceController::class, 'show']); // Show a specific device
        Route::put('devices/{id}', [DeviceController::class, 'update']); // Update a specific device
        Route::delete('devices/{id}', [DeviceController::class, 'destroy']); // Delete a specific device

        Route::get('power-data', [PowerDataController::class, 'getPowerData']); // List all users


        Route::get('power-data-sync-log', [PowerDataController::class, 'getAllLogs']);

        Route::get('power-data-sync-log-last', [PowerDataController::class, 'getLastSyncedLog']);
    });
});




Route::prefix('client')->group(function () {
    Route::post('register', [ClientAuthController::class, 'register']);

    Route::post('login', [ClientAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [ClientController::class, 'getClientProfile']);
        Route::put('profile', [ClientController::class, 'updateClientProfile']);
        Route::put('reset-password', [ClientAuthController::class, 'resetPassword']);
        Route::post('logout', [ClientAuthController::class, 'logout']);

        Route::put('update-child-client/{client_remotik_id}/{child_client_remotik_id}', [ChildClientController::class, 'updateChildClientProfile']);

        Route::post('become-seller/{clientId}', [SellerController::class, 'becomeSeller']);
        Route::get('seller/{clientId}', [SellerController::class, 'getSellerInfo']);
        Route::put('seller/{clientId}', [SellerController::class, 'updateSellerInfo']);
    });
});


// ------------------SQLITE API----------------

Route::prefix('sqlite')->group(function () {
    Route::get('/main', [SqliteController::class, 'main']);
    Route::get('/power', [SqliteController::class, 'power']);


});

Route::get('/sync', [PowerDataController::class, 'syncSqlite']);



Route::prefix('invoice')->group(function () {

    Route::get('/download/{invoice_id}', [InvoiceController::class, 'downloadInvoice']);

    Route::get('/preview/{invoice_id}', [InvoiceController::class, 'previewInvoice']);
    Route::get('/list', [InvoiceController::class, 'getInvoices']);
    Route::get('/view/{invoice_id}', [InvoiceController::class, 'viewInvoice']);
    Route::put('/update/{invoice_id}', [InvoiceController::class, 'updateInvoice']);
    Route::delete('/delete/{invoice_id}', [InvoiceController::class, 'deleteInvoice']);


    Route::middleware(['auth:admin-api'])->group(function () {

        Route::post('/generate', [InvoiceController::class, 'generateInvoice']);




    });

});


Route::prefix('client/invoice')->group(function () {

    Route::middleware(['auth:sanctum', 'auth:client-api'])->group(function () {

        /* clients invoice api */
        Route::post('/generate', [InvoiceChildClientController::class, 'generateChildClientInvoice']);

        /* for child clients */
        Route::get('/child-client-invoice-list/{client_remotik_id}', [InvoiceChildClientController::class, 'getChildClientInvoices']);

         /* for child clients */
         Route::get('/invoice-for/{client_remotik_id}', [InvoiceChildClientController::class, 'invoiceForClient']);


    });

});




Route::get('/clients/nodejs', [ClientController::class, 'getClientsFromNodeJS']);

Route::get('/clients/array', [ClientController::class, 'getClientsArray']);


Route::get('/client/child', [ChildClientController::class, 'getChildClients']);

Route::get('client/child/profile/{client_remotik_id}/{child_client_remotik_id}', [ChildClientController::class, 'getChildClientProfile']);


// sync nodes
Route::post('/sync-nodes', [NodesController::class, 'syncNodes']);

Route::get('/nodes', [NodesController::class, 'getNodes']);
