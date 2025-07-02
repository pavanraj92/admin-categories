<?php

use Illuminate\Support\Facades\Route;
use admin\categories\Controllers\CategoryManagerController;

Route::name('admin.')->middleware(['web','auth:admin'])->group(function () {  
    Route::middleware('auth:admin')->group(function () {
        Route::resource('categories', CategoryManagerController::class);
        Route::post('categories/updateStatus', [CategoryManagerController::class, 'updateStatus'])->name('categories.updateStatus');
    });
});
