<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\VisitorCardController;
use App\Http\Controllers\CardTransactionController;
use App\Http\Controllers\VisitorCardStatusLogController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\VisitTypeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationLogController;


//user
Route::prefix('public')->group(function () {
    // Landing page 
    Route::get('/visit-types', [VisitTypeController::class, 'index']);
    Route::get('/stations', [StationController::class, 'index']);

    // Ajukan permohonan kartu visitor
    Route::post('/visitor-cards', [VisitorCardController::class, 'store']);
    // Konfirmasi data & detail status pengajuan
    Route::get('/visitor-cards/{id}', [VisitorCardController::class, 'show']);

    // Cek status pengajuan (by reference number)
    Route::post('/check-status', [VisitorCardController::class, 'checkStatus']);
    Route::get('/visitor-cards/{id}/detail', [VisitorCardController::class, 'show']);
    Route::get('/status-logs', [VisitorCardStatusLogController::class, 'index']);

    // Batalkan & ajukan ulang pengajuan
    Route::post('/cancel-application', [VisitorCardController::class, 'cancel']);
    Route::post('/resubmit-application', [VisitorCardController::class, 'resubmit']);
});


//admin
Route::prefix('admin')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        // Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('/active-visitors', [DashboardController::class, 'getActiveVisitors']);
            Route::get('/pending-count', [DashboardController::class, 'getPendingCount']);
            Route::get('/today-issued', [DashboardController::class, 'getTodayIssued']);
            Route::get('/today-returned', [DashboardController::class, 'getTodayReturned']);
            Route::get('/damaged-cards', [DashboardController::class, 'getDamagedCards']);
            Route::get('/lost-cards', [DashboardController::class, 'getLostCards']);
        });

        // Verifikasi 
        Route::prefix('verification')->group(function () {
            Route::get('/pending', [VisitorCardController::class, 'getPending']);
            Route::post('/detail', [VisitorCardController::class, 'detailByReference']);
            Route::post('/approve', [VisitorCardController::class, 'approve']);
            Route::post('/reject', [VisitorCardController::class, 'reject']);
            Route::post('/bulk-action', [VisitorCardController::class, 'bulkAction']);
        });

        // Kartu visitor 
        Route::prefix('cards')->group(function () {
            Route::get('/approved', [CardTransactionController::class, 'listApproved']);
            Route::get('/active', [CardTransactionController::class, 'listActive']);
            Route::get('/returned', [CardTransactionController::class, 'listReturned']);
            Route::post('/issue', [CardTransactionController::class, 'issue']);
            Route::post('/return', [CardTransactionController::class, 'return']);
            Route::put('/{id}/condition', [CardTransactionController::class, 'editCondition']);
        });

        // Export & reporting
        Route::prefix('reports')->group(function () {
            Route::get('/station-daily-flow', [ReportController::class, 'exportStationDailyFlow']);
            Route::get('/export-all', [ReportController::class, 'exportAll']);
            Route::get('/daily-flow', [ReportController::class, 'exportDailyFlow']);
            Route::get('/weekly-flow', [ReportController::class, 'exportWeeklyFlow']);
            Route::get('/monthly-flow', [ReportController::class, 'exportMonthlyFlow']);
            Route::get('/yearly-flow', [ReportController::class, 'exportYearlyFlow']);
            Route::get('/card-condition', [ReportController::class, 'exportCardCondition']);
        });

        // CRUD resources
        Route::apiResource('visitor-cards', VisitorCardController::class)->except(['store']);
        Route::apiResource('card-transactions', CardTransactionController::class);
        Route::apiResource('status-logs', VisitorCardStatusLogController::class);
        Route::apiResource('stations', StationController::class);
        Route::apiResource('visit-types', VisitTypeController::class);
        Route::apiResource('users', UserController::class);
        Route::apiResource('notifications', NotificationLogController::class);
    });
});


Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
