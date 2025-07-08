<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CertifController;

Route::get('/', function () {
    return view('welcome');
});