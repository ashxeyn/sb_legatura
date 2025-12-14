<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\authController;
use App\Http\Controllers\Owner\projectsController;
use App\Http\Controllers\projectPosting\projectPostingController;

// Test endpoint for mobile app
Route::get('/test', [authController::class, 'apiTest']);

// Authentication routes (no CSRF protection)
Route::post('/login', [authController::class, 'apiLogin']);
Route::post('/register', [authController::class, 'apiRegister']);
Route::get('/signup-form', [authController::class, 'showSignupForm']);
Route::post('/role-select', [authController::class, 'selectRole']);

// Address/Location endpoints for mobile app
Route::get('/provinces', [authController::class, 'getProvinces']);
Route::get('/provinces/{provinceCode}/cities', [authController::class, 'getCitiesByProvince']);
Route::get('/cities/{cityCode}/barangays', [authController::class, 'getBarangaysByCity']);

// Contractors endpoint for property owner feed
Route::get('/contractors', [projectsController::class, 'apiGetContractors']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Additional protected endpoints can be added here
    Route::get('projects', [projectPostingController::class, 'apiIndex'])->name('api.projects.index');
    Route::post('projects', [projectPostingController::class, 'apiStore'])->name('api.projects.store');
    Route::get('projects/{project}', [projectPostingController::class, 'apiShow'])->name('api.projects.show');
    Route::put('projects/{project}', [projectPostingController::class, 'apiUpdate'])->name('api.projects.update');
    Route::patch('projects/{project}', [projectPostingController::class, 'apiUpdate'])->name('api.projects.update');
    Route::delete('projects/{project}', [projectPostingController::class, 'apiDestroy'])->name('api.projects.destroy');
});
