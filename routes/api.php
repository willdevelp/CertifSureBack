<?php
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\CertifController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScanStatsController;
use Illuminate\Http\Request;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // });
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'getProfile']); // Récupérer le profil
Route::put('/update', [AuthController::class, 'updateUser']); // Mettre à jour le profil
Route::delete('/profile/delete', [UserController::class, 'deleteProfile']); // Supprimer le profil
// Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'getAuthenticatedUser']);
// Route::middleware('auth:sanctum')->put('/user/update', [UserController::class, 'updateUser']);

// Route::post('/promotion', [PromotionsController::class, 'store']);
// Route::get('/promotion', [PromotionsController::class, 'index']);
// Route::get('/promotion/{id}', [PromotionsController::class, 'show']);
// Route::delete('/promotion/{id}', [PromotionsController::class, 'destroy']);

Route::get('/stats', [ScanStatsController::class, 'getScanStats'])->name('api.scans.stats');

Route::post('/upload-files', [CertifController::class, 'uploadFiles']);
Route::get('/upload-files/{reference_number}', [CertifController::class, 'show'])->name('api.ref.show');
Route::get('/certifs', [CertifController::class, 'getCertifs']);
Route::get('/download-file/{reference_number}', [CertifController::class, 'download']);
Route::delete('/delete-file/{reference_number}', [CertifController::class, 'delete']);

Route::post('/handle-scan', [CertifController::class, 'handleScan']);
Route::get('/scans', [ScanController::class, 'getAllScans']);
Route::get('/scan-stats', [ScanController::class, 'getScanStats']);
});
Route::get('/upload-files/{reference_number}', [CertifController::class, 'show'])->name('api.ref.show');
Route::get('/download-file/{reference_number}', [CertifController::class, 'download']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset']);


?>
