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
                'id_meta' => 'required|integer',
                'titulo' => 'required|string',
                'descricao' => 'sometimes|string|nullable',
                'status' => 'sometimes|string|in:pendente,concluida',
            ]);

            $usuario = Auth::user();
            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }
            $tarefa = $this->service->criarTarefa(
                $usuario->id_usuario,
                $request->input('id_meta'),
                $request->input('titulo'),
                $request->input('descricao'),
                $request->input('status', 'pendente')
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

            $id_meta = request()->query('id_meta');
            $tarefas = $this->service->listarTarefas($usuario->id_usuario, $id_meta ? (int)$id_meta : null);
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
                'descricao' => 'sometimes|string|nullable',
                'status' => 'sometimes|string|in:pendente,concluida',
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
