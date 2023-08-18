<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BucketCubesController;

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

Route::get('/', function () {
    return view('welcome');
});
Route::post('storeData', [BucketCubesController::class, 'storeData'])->name('storeData');
Route::get('getAllBucketsWithVolume', [BucketCubesController::class, 'getAllBucketsWithVolume'])->name('getAllBucketsWithVolume');
Route::get('getAllBallsWithVolume', [BucketCubesController::class, 'getAllBallsWithVolume'])->name('getAllBallsWithVolume');
Route::get('getAllPlacedBalls', [BucketCubesController::class, 'getAllPlacedBalls'])->name('getAllPlacedBalls');
Route::post('placeBalls', [BucketCubesController::class, 'placeBalls'])->name('placeBalls');
