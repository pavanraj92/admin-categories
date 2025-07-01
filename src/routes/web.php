<?php

use Illuminate\Support\Facades\Route;
use admin\category\Controllers\CategoryManagerController;

Route::name('admin.')->middleware(['web','auth:admin'])->group(function () {  
    Route::middleware('auth:admin')->group(function () {
        Route::resource('category', CategoryManagerController::class);
        Route::post('category/updateStatus', [CategoryManagerController::class, 'updateStatus'])->name('category.updateStatus');
    });
});
