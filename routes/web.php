<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

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

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    // Plans
    Route::resource('plans', PlanController::class);

    // Subscriptions
    Route::resource('subscriptions', SubscriptionController::class);

    // Invoices
    Route::resource('invoices', InvoiceController::class);

    // Payments
    Route::resource('payments', PaymentController::class);
});

// Маршруты аутентификации
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');

    // Маршруты для администратора
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('clients', function () {
            return view('admin.clients.index');
        })->name('clients.index');
    });
});

// Маршруты админки
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('clients', \App\Http\Controllers\Admin\ClientController::class);
    Route::patch('clients/{client}/toggle-status', [\App\Http\Controllers\Admin\ClientController::class, 'toggleStatus'])->name('clients.toggle-status');
    Route::post('clients/{client}/reset-database', [\App\Http\Controllers\Admin\ClientController::class, 'resetDatabase'])->name('clients.reset-database');
});
