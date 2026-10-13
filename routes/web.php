<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PainelController;

Route::get('/', [PainelController::class, 'index']);

Route::view('/unidades', 'unidades');

Route::view('/despesas', 'despesas');

Route::view('/importacao', 'importacao');

Route::view('/relatorios', 'relatorios');