<?php

use Illuminate\Support\Facades\Route;
use Modules\TwoFactor\Http\Controllers\MfaChallengeController;
use Modules\TwoFactor\Http\Controllers\TwoFactorController;

Route::prefix('v1/security')->group(function () {
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('mfa', [TwoFactorController::class, 'status']);
        Route::post('mfa/setup', [TwoFactorController::class, 'setup']);
        Route::post('mfa/confirm', [TwoFactorController::class, 'confirm']);
        Route::post('mfa/recovery-codes', [TwoFactorController::class, 'regenerateRecoveryCodes']);
        Route::delete('mfa', [TwoFactorController::class, 'disable']);
    });

    Route::post('mfa/challenge', [MfaChallengeController::class, 'verify']);
});
