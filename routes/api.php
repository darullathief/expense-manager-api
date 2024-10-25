<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BudgetsController;
use App\Http\Controllers\CategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('user/register', [UserController::class, 'register']);
Route::post('user/login', [UserController::class, 'login']);
Route::post('category/add', [CategoryController::class, 'add']);
Route::post('category/get', [CategoryController::class, 'get']);
Route::post('category/edit', [CategoryController::class, 'edit']);
Route::post('category/delete', [CategoryController::class, 'delete']);
Route::post('budgets/add', [BudgetsController::class, 'add']);
Route::post('budgets/edit', [BudgetsController::class, 'edit']);
Route::post('budgets/delete', [BudgetsController::class, 'delete']);
Route::post('budgets/get', [BudgetsController::class, 'get']);