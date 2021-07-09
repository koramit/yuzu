<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Inertia\Inertia::render('Welcome');
});

Route::get('/prototypes/form', function () {
    return Inertia\Inertia::render('Prototypes/Form');
});
