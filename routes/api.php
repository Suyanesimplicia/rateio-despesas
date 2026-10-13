<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UnidadeController;
use App\Http\Controllers\API\DespesaController;
use App\Http\Controllers\API\ImportacaoController;
use App\Http\Controllers\API\RelatorioController;

Route::apiResource('unidades', UnidadeController::class);
Route::post('/despesas/importar', [importacaoController::class, 'importar']);
Route::apiResource('despesas', DespesaController::class);
Route::get('/relatorios/unidades', [RelatorioController::class, 'porunidade']);
