<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\TarefaController;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/usuario', [AuthController::class, 'usuarioAutenticado']);
});

Route::prefix('usuarios')->group(function () {
    Route::post('/criar', [UsuarioController::class, 'criar']);
    Route::get('/listar', [UsuarioController::class, 'listar']);
    Route::put('/atualizar/{id}', [UsuarioController::class, 'atualizar']);
});

Route::group(['middleware'], function () {
    Route::prefix('tarefas')->group(function () {
        Route::post('/criar', [TarefaController::class, 'criar']);
        Route::get('/listar', [TarefaController::class, 'listar']);
        Route::put('/atualizar/{id}', [TarefaController::class, 'atualizar']);
        Route::delete('/deletar/{id}', [TarefaController::class, 'deletar']);
        Route::get('/pesquisar', [TarefaController::class, 'pesquisar']);
    });
});
