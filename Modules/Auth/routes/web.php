<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AuthController;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/workspace/profile', [AuthController::class, 'profile'])->name('workspace.profile');
    Route::put('/workspace/profile', [AuthController::class, 'updateProfile'])->name('workspace.profile.update');

    Route::resource('users', AuthController::class)->names('admin.users');
});
