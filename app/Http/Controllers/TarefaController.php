<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Services\TarefaService;
use Illuminate\Support\Facades\Auth;



class TarefaController extends Controller
{
    protected $service;

    public function __construct(TarefaService $service)
    {
        $this->service = $service;
    }

    public function criar(Request $request)
    {
        try {
            $request->validate([
                'titulo' => 'required|string',
                'importancia' => 'required|string',
                'status' => 'required|string',
                'ativo' => 'required|boolean',
            ]);

            $usuario = Auth::user();
            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }
            $tarefa = $this->service->criarTarefa(
                $request->input('titulo'),
                $request->input('importancia'),
                $request->input('status'),
                $request->input('ativo'),
                $usuario->id_usuario
            );

            return ResponseHelper::success($tarefa, 'Tarefa criada com sucesso', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao criar tarefa: ' . $e->getMessage(), 500);
        }
    }

    public function listar()
    {
        try {
            $usuario = Auth::user();

            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }


            $tarefas = $this->service->listarTarefas($usuario->id_usuario);
            return ResponseHelper::success($tarefas, 'Tarefas listadas com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao listar tarefas: ' . $e->getMessage(), 500);
        }
    }

    public function atualizar(Request $request, int $id_tarefa)
    {
        try {
            $request->validate([
                'titulo' => 'sometimes|string',
                'importancia' => 'sometimes|string|in:baixa,media,alta',
                'status' => 'sometimes|string|in:pendente,em_andamento,concluida',
                'ativo' => 'sometimes|boolean',
            ]);

            $usuario = Auth::user();

            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }

            $tarefaAtualizada = $this->service->atualizarTarefa(
                $id_tarefa,
                $request->all(),
                $usuario->id_usuario
            );

            return ResponseHelper::success($tarefaAtualizada, 'Tarefa atualizada com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao atualizar tarefa: ' . $e->getMessage(), 500);
        }
    }


    public function deletar(int $id)
    {
        try {
            if (empty($id)) {
                return ResponseHelper::error('Tarefa não informada', 400);
            }

            $this->service->deletarTarefa($id);
            return ResponseHelper::success(null, 'Tarefa deletada com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao deletar tarefa: ' . $e->getMessage(), 500);
        }
    }

    public function pesquisar(Request $request)
    {
        try {
            $termo = $request->query('q');
            $tarefas = $this->service->pesquisar($termo);


            if ($tarefas->isEmpty()) {
                return ResponseHelper::error('Nenhuma tarefa encontrada', 404);
            }

            return ResponseHelper::success(
                $tarefas,
                'Pesquisa realizada com sucesso',
                200
            );
        } catch (\Exception $e) {
            return ResponseHelper::error(
                'Erro ao pesquisar tarefas: ' . $e->getMessage(),
                500
            );
        }
    }
}
