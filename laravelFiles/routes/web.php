<?php

use App\Http\Controllers\PageLoader;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageLoader::class,'home']);