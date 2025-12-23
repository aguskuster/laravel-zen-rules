<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\ExpressionConfig;

Route::get('/', function () {
    return view('home');
});

Route::get('/graph', function () {
    return view('graph');
});

Route::get('/expression-test', function () {
    return view('expression-test');
});
