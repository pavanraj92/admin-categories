<?php

use Illuminate\Support\Facades\Route;
use admin\categories\Controllers\CategoryManagerController;

Route::name('admin.')->middleware(['web','admin.auth'])->group(function () {  
    Route::resource('categories', CategoryManagerController::class);
    Route::post('categories/updateStatus', [CategoryManagerController::class, 'updateStatus'])->name('categories.updateStatus');

});
