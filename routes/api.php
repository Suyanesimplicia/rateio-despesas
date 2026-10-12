<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UnidadeController;
use App\Http\Controllers\API\DespesaController;

Route::apiResource('unidades', UnidadeController::class);
Route::apiResource('despesas', DespesaController::class);
