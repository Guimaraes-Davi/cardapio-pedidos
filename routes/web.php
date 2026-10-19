<?php

use Illuminate\Support\Facades\Route;

// SPA: qualquer URL fora de /api é entregue ao Vue, que decide a tela pelo vue-router
Route::view('/{any?}', 'app')->where('any', '^(?!api/|up$).*');
