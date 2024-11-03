<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BudgetsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;


Route::get('user', [UserController::class, 'index']);
Route::post('user/register', [UserController::class, 'register']);
Route::post('user/login', [UserController::class, 'login']);
Route::post('category/add', [CategoryController::class, 'add']);
Route::get('category/get', [CategoryController::class, 'get']);
Route::post('category/edit', [CategoryController::class, 'edit']);
Route::post('category/delete', [CategoryController::class, 'delete']);
Route::post('budgets/add', [BudgetsController::class, 'add']);
Route::post('budgets/edit', [BudgetsController::class, 'edit']);
Route::post('budgets/delete', [BudgetsController::class, 'delete']);
Route::get('budgets/get', [BudgetsController::class, 'get']);
Route::get('budgets/get_month_calculation', [BudgetsController::class, 'get_month_calculation']);
Route::post('transaction/add', [TransactionController::class, 'add']);
Route::post('transaction/edit', [TransactionController::class, 'edit']);
Route::post('transaction/delete', [TransactionController::class, 'delete']);
Route::get('transaction/get', [TransactionController::class, 'get']);
Route::get('transaction/get_calculation', [TransactionController::class, 'get_calculation']);
Route::post('transaction/balance', [TransactionController::class, 'balance']);