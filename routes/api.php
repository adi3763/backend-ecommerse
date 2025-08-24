<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\BrandsController;
use App\Http\Controllers\admin\CategoriesController;
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


});
