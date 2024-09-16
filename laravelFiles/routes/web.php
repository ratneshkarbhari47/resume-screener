<?php

use App\Http\Controllers\PageLoader;
use App\Http\Controllers\Services;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageLoader::class,'home']);

Route::post("screen-resume-service",[Services::class,'analyze_resumes']);