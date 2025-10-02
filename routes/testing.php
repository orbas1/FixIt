<?php

use App\Http\Controllers\Testing\ViewPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/__dusk/view-preview', ViewPreviewController::class)
    ->name('testing.view-preview');
