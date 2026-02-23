<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\TarefaController;
use App\Http\Controllers\MetaController;

Route::get('/', function () {
    return response()->json([
        'status' => 'online'
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'usuarioAutenticado']);
    Route::get('/usuario', [AuthController::class, 'usuarioAutenticado']);
});

Route::prefix('usuarios')->group(function () {
    Route::post('/criar', [UsuarioController::class, 'criar']);
    Route::get('/listar', [UsuarioController::class, 'listar']);
    Route::put('/atualizar/{id}', [UsuarioController::class, 'atualizar']);
    Route::put('/me', [UsuarioController::class, 'atualizarMe']);
});

Route::group(['middleware'], function () {
    Route::prefix('tarefas')->group(function () {
        Route::post('/criar', [TarefaController::class, 'criar']);
        Route::get('/listar', [TarefaController::class, 'listar']);
        Route::put('/atualizar/{id}', [TarefaController::class, 'atualizar']);
        Route::delete('/deletar/{id}', [TarefaController::class, 'deletar']);
        Route::get('/pesquisar', [TarefaController::class, 'pesquisar']);
    });

    Route::prefix('metas')->group(function () {
        Route::get('/resumo', [MetaController::class, 'resumo']);
        Route::post('/criar', [MetaController::class, 'criar']);
        Route::get('/listar', [MetaController::class, 'listar']);
        Route::get('/detalhes/{id}', [MetaController::class, 'detalhes']);
        Route::put('/atualizar/{id}', [MetaController::class, 'atualizar']);
        Route::delete('/deletar/{id}', [MetaController::class, 'deletar']);

        // tarefas dentro de metas
        Route::post('/{id_meta}/tarefas/criar', [MetaController::class, 'criarTarefa']);
        Route::put('/{id_meta}/tarefas/{id_tarefa}/status', [MetaController::class, 'atualizarStatusTarefa']);
    });
});
