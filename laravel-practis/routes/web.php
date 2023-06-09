<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [\App\Http\Controllers\CsvExportRecordController::class, 'store'])->name('users.csv-export-records.store');

    Route::get('users/csv-export-records', [\App\Http\Controllers\CsvExportRecordController::class, 'index'])->name('users.csv-export-records.index');
    Route::get('users/csv-export-records/{id}', [\App\Http\Controllers\CsvExportRecordController::class, 'download'])->name('users.csv-export-records.download');

    Route::resource('companies', \App\Http\Controllers\CompanyController::class);
    Route::resource('companies.sections', \App\Http\Controllers\SectionController::class);
    Route::resource('sections.users', \App\Http\Controllers\SectionUserController::class)->only(['store', 'destroy']);
});

require __DIR__ . '/auth.php';
