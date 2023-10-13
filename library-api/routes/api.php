<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


//Book functions
Route::get('book-list', [LibraryController::class, 'index']);

Route::get('single-book-data/{id}', [LibraryController::class, 'bookById']);

Route::post('add-book', [LibraryController::class, 'create']);

Route::delete('remove-book/{id}', [LibraryController::class, 'destroy']);

Route::post('update-book/{id}', [LibraryController::class, 'update']);

Route::post('reserve-book', [LibraryController::class, 'reserveBook']);

Route::post('return-book', [LibraryController::class, 'returnBook']);

Route::get('reserved-book-list/{id}', [LibraryController::class, 'reservedBooksById']);

//Admin
Route::post('admin-login', [AdminController::class, 'login']);

Route::post('admin-auth', [AdminController::class, 'routerAuth']);

//User function
Route::post('login', [UserController::class, 'login']);

Route::post('register', [UserController::class, 'register']);

Route::post('verify-token', [UserController::class, 'verifyToken']);

Route::post('find-by-token', [UserController::class, 'findByToken']);

//Route::get('user-list', [UserController::class, 'index']);


