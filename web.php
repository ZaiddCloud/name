<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});


Route::resource('books', BookController::class);
Route::post('/books/update-order', [BookController::class, 'updateOrder'])->name('books.updateOrder');


