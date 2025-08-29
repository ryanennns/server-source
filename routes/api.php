<?php

use App\Http\Controllers\GetWorldGenSettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('{ec2_user_id}', GetWorldGenSettingsController::class);
