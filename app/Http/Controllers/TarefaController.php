<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Services\TarefaService;

use function PHPUnit\Framework\isEmpty;

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

            $tarefa = $this->service->criarTarefa(
                $request->input('titulo'),
                $request->input('importancia'),
                $request->input('status'),
                $request->input('ativo')
            );

            return ResponseHelper::success($tarefa, 'Tarefa criada com sucesso', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao criar tarefa: ' . $e->getMessage(), 500);
        }
    }

    public function listar()
    {
        try {
            $tarefas = $this->service->listarTarefas();
            return ResponseHelper::success($tarefas, 'Tarefas listadas com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao listar tarefas: ' . $e->getMessage(), 500);
        }
    }

    public function atualizar(Request $request, int $id)
    {
        try {
            $request->validate([
                'titulo' => 'sometimes|string',
                'importancia' => 'sometimes|string',
                'status' => 'sometimes|string',
                'ativo' => 'sometimes|boolean',
            ]);

            $dados = $request->only(['titulo', 'importancia', 'status', 'ativo']);
            $tarefaAtualizada = $this->service->atualizarTarefa($id, $dados);

            return ResponseHelper::success([$tarefaAtualizada], 'Tarefa atualizada com sucesso', 200);
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
}
