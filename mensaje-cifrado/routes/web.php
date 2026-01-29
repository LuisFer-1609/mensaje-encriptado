<?php

use App\Http\Controllers\EncryptionController; /*Libreria para RSA*/
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return Inertia::render('Welcome', [
//         'canLogin' => Route::has('login'),
//         'canRegister' => Route::has('register'),
//         'laravelVersion' => Application::VERSION,
//         'phpVersion' => PHP_VERSION,
//     ]);
// });

Route::get('/', function () {
    return Inertia::render('Email/Home');
})->middleware(['auth', 'verified'])->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
/*Ruta para mandar la incriptacion*/
Route::get('/encriptar', [EncryptionController::class, 'createKey']);
Route::post('/desencriptar', [EncryptionController::class, 'desencriptar']);

use App\Http\Controllers\MessageController;
Route::middleware('auth')->get('/messages', [MessageController::class, 'index'])->name('messages.index');
Route::middleware('auth')->post('/messages', [MessageController::class, 'store'])->name('messages.store');
Route::post('/check-email', [MessageController::class, 'checkEmail'])->name('check.email');

/*Marcar mensaje como leido*/
Route::middleware('auth')->patch('/messages/read/{id}', [MessageController::class, 'markAsRead'])->name('messages.markAsRead');


require __DIR__ . '/auth.php';
