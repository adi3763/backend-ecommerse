<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\BrandsController;
use App\Http\Controllers\admin\CategoriesController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\SizeController;
use App\Http\Controllers\admin\TempImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('admin/login',[AuthController::class, 'login']);
Route::middleware('auth:sanctum')->group(function(){

    /* CATEGORY API */
    Route::get('admin/category',[CategoriesController::class,'index']);
    Route::post('admin/category',[CategoriesController::class,'store']);
    Route::get('admin/category/{id}',[CategoriesController::class,'show']);
    Route::put('admin/category/{id}',[CategoriesController::class,'update']);
    Route::delete('admin/category/{id}',[CategoriesController::class,'destroy']);


    /* BRANDS API */
    Route::get('admin/brand',[BrandsController::class,'index']);
    Route::post('admin/brand',[BrandsController::class,'store']);
    Route::get('admin/brand/{id}',[BrandsController::class,'show']);
    Route::put('admin/brand/{id}',[BrandsController::class,'update']);
    Route::delete('admin/brand/{id}',[BrandsController::class,'destroy']);

    /*PRODUCTS API */
    Route::get('admin/products',[ProductController::class,'index']);
    Route::post('admin/products',[ProductController::class,'store']);
    Route::get('admin/products/{id}',[ProductController::class,'show']);
    Route::put('admin/products/{id}',[ProductController::class,'update']);
    Route::delete('admin/products/{id}',[ProductController::class,'destroy']);

     /*Sizes API */
     Route::get('admin/size',[SizeController::class,'show']);

     /*TEMP IMAGES API */
     Route::post('admin/temp-image',[TempImageController::class,'store']);

});
