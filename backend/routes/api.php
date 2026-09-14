<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DataSourceController;
use App\Http\Controllers\Api\Internal\PositionIngestionController;
use App\Http\Controllers\Api\Internal\VesselWhitelistController;
use App\Http\Controllers\Api\Internal\WorkerHeartbeatController;
use App\Http\Controllers\Api\OperatorController;
use App\Http\Controllers\Api\PortController;
use App\Http\Controllers\Api\Public\PublicPortController;
use App\Http\Controllers\Api\Public\PublicPortEventController;
use App\Http\Controllers\Api\Public\PublicPositionController;
use App\Http\Controllers\Api\Public\PublicPositionHistoryController;
use App\Http\Controllers\Api\Public\PublicRouteController;
use App\Http\Controllers\Api\Public\PublicVesselController;
use App\Http\Controllers\Api\RegistryEvidenceController;
use App\Http\Controllers\Api\RouteController;
use App\Http\Controllers\Api\VesselController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('vessels', [PublicVesselController::class, 'index']);
    Route::get('vessels/{vessel}', [PublicVesselController::class, 'show']);
    Route::get('vessels/{vessel}/positions/history', [PublicPositionHistoryController::class, 'history']);
    Route::get('positions/latest', [PublicPositionController::class, 'latest']);
    Route::get('ports', [PublicPortController::class, 'index']);
    Route::get('ports/{port}', [PublicPortController::class, 'show']);
    Route::get('ports/{port}/events', [PublicPortEventController::class, 'index']);
    Route::get('routes', [PublicRouteController::class, 'index']);
    Route::get('routes/{route}', [PublicRouteController::class, 'show']);
});

Route::prefix('v1/admin')->middleware('auth:sanctum')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login'])
        ->withoutMiddleware('auth:sanctum')
        ->middleware('throttle:5,1');
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::apiResource('operators', OperatorController::class);
    Route::apiResource('vessels', VesselController::class);
    Route::apiResource('data-sources', DataSourceController::class);
    Route::apiResource('ports', PortController::class);
    Route::apiResource('routes', RouteController::class);

    Route::post('vessels/{vessel}/verify', [VesselController::class, 'verify']);
    Route::post('vessels/{vessel}/reject', [VesselController::class, 'reject']);

    Route::apiResource('vessels.evidence', RegistryEvidenceController::class)->scoped();

    Route::get('audit-logs', [AuditLogController::class, 'index']);
});

Route::prefix('internal/v1')->middleware('internal')->group(function () {
    Route::get('vessel-whitelist', [VesselWhitelistController::class, 'index']);
    Route::post('positions', [PositionIngestionController::class, 'store']);
    Route::post('worker-heartbeat', [WorkerHeartbeatController::class, 'store']);
});
