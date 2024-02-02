<?php
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
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

Route::get('/', [DashboardController::class, 'showData'])->middleware(['auth', 'verified'])->name('dashboard');
// Route::get('/dashboard', function () {
//     return Inertia::render('Dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');


// API
Route::post('/create-configurator', [DashboardController::class, 'create_new'])->middleware(['auth', 'verified']);
Route::post('/delete-configurator', [DashboardController::class, 'deleteConfigurator'])->middleware(['auth', 'verified']);
// API configurator
Route::get('/configurator/{id_c}', [DashboardController::class, 'view_config'])->middleware(['auth', 'verified'])->name('configurator');
Route::post('/saveProduct', [DashboardController::class, 'save_product'])->middleware(['auth', 'verified']);

// API END

Route::get('/dashboard', [DashboardController::class, 'showData'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/get-product/{product}', [DashboardController::class, 'getProduct_data']);
Route::get('/storage/image/{filename}', [DashboardController::class, 'showImage'])->name('image.show');
Route::post('/upload-image', [DashboardController::class, 'uploadImage']);

require __DIR__.'/auth.php';
