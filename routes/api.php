<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\UserController;

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

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [LoginController::class, 'logout']);
Route::post('/users/assign-role/{userId}', [UserController::class, 'assignRoleToUser']);


Route::post('/books', [BooksController::class, 'store']);
Route::get('/books', [BooksController::class, 'getBooks']);
Route::get('/books/{id}', [BooksController::class, 'show']);
Route::post('/books-update/{id}', [BooksController::class, 'update']);
Route::delete('/books/{id}', [BooksController::class, 'destroy']);
Route::get('/books-filter', [BooksController::class, 'getBooksFilter']);
Route::get('/search', [BooksController::class, 'search']);



