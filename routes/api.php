<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IeltsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);


Route::middleware(['auth:api'])->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::prefix('ielts')->group(function () {
        Route::get('/questions', [IeltsController::class, 'index']);      
        Route::get('/questions/{id}', [IeltsController::class, 'show']);  
        Route::post('/submit', [IeltsController::class, 'submit']);       
    });

    Route::middleware(['admin'])->prefix('admin/ielts')->group(function () {
        Route::post('/essays', [IeltsController::class, 'store']);        
        Route::put('/essays/{id}', [IeltsController::class, 'update']);   
        Route::delete('/essays/{id}', [IeltsController::class, 'destroy']); 
    });
});