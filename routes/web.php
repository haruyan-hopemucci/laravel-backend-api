<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*
    Sanctum未認証時に/loginへリダイレクトが発生するため、当面の処置として`/`へ飛ばす処理を入れておく
*/

Route::get('/', function () {
    return view('welcome');
})->name('login');
