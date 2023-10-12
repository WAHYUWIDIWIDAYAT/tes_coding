<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Invoice\ProductController;
use App\Http\Controllers\Invoice\InvoiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//group v1/
Route::prefix('v1')->group(function () {
    Route::prefix('invoice')->group(function () {
        //get all invoice
        Route::get('/', [InvoiceController::class, 'index']);
        //create new invoice
        Route::post('/', [InvoiceController::class, 'store']);
        //show data by query invoice number
        Route::get('/details', [InvoiceController::class, 'show']);
        //update data by query invoice number
        Route::put('/update', [InvoiceController::class, 'update']);
        //delete data by query invoice number
        Route::delete('/delete', [InvoiceController::class, 'destroy']);
    });

    Route::prefix('product')->group(function () {
        //get all product
        Route::get('/', [ProductController::class, 'index']);
        //create new product
        Route::post('/', [ProductController::class, 'store']);
        //show data by id
        Route::get('/{id}', [ProductController::class, 'show']);
        //update data by id
        Route::put('/{id}', [ProductController::class, 'update']);
        //delete data by id
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });
});

