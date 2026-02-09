<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Services\MetaService;
use App\Services\TarefaService;
use Illuminate\Support\Facades\Auth;

class MetaController extends Controller
{
    public function __construct(private MetaService $metaService, private TarefaService $tarefaService)
    {
    }

    public function criar(Request $request)
    {
        try {
            $request->validate([
                'titulo' => 'required|string',
                'descricao' => 'sometimes|string|nullable',
                'contexto' => 'sometimes|string|nullable',
                'prioridade' => 'sometimes|string|in:baixa,media,alta',
                'tipo' => 'required|string|in:diaria,mensal,anual',
                // aceita strings em d/m/Y ou Y-m-d; normalização é feita no service
                'data_inicio' => 'sometimes|string|nullable',
                'data_fim' => 'sometimes|string|nullable',
            ]);

            $usuario = Auth::user();
            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }

            $meta = $this->metaService->criarMeta($request->all(), $usuario->id_usuario);
            return ResponseHelper::success($meta, 'Meta criada com sucesso', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao criar meta: ' . $e->getMessage(), 500);
        }
    }

    public function listar(Request $request)
    {
        try {
            $usuario = Auth::user();
            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }
            $tipo = $request->query('tipo');
            $metas = $this->metaService->listarMetas($usuario->id_usuario, $tipo);
            return ResponseHelper::success($metas, 'Metas listadas com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao listar metas: ' . $e->getMessage(), 500);
        }
    }

    public function detalhes(int $id)
    {
        try {
            $usuario = Auth::user();
            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }
            $detalhes = $this->metaService->detalhesMeta($id, $usuario->id_usuario);
            return ResponseHelper::success($detalhes, 'Detalhes da meta', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao obter detalhes da meta: ' . $e->getMessage(), 500);
        }
    }

    public function atualizar(Request $request, int $id)
    {
        try {
            $request->validate([
                'titulo' => 'sometimes|string',
                'descricao' => 'sometimes|string|nullable',
                'contexto' => 'sometimes|string|nullable',
                'prioridade' => 'sometimes|string|in:baixa,media,alta',
                'tipo' => 'sometimes|string|in:diaria,mensal,anual',
                'data_inicio' => 'sometimes|string|nullable',
                'data_fim' => 'sometimes|string|nullable',
            ]);
            $usuario = Auth::user();
            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }
            $meta = $this->metaService->atualizarMeta($id, $request->all(), $usuario->id_usuario);
            return ResponseHelper::success($meta, 'Meta atualizada com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao atualizar meta: ' . $e->getMessage(), 500);
        }
    }

    public function deletar(int $id)
    {
        try {
            $usuario = Auth::user();
            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }
            $this->metaService->deletarMeta($id, $usuario->id_usuario);
            return ResponseHelper::success(null, 'Meta deletada com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao deletar meta: ' . $e->getMessage(), 500);
        }
    }

    public function criarTarefa(Request $request, int $id_meta)
    {
        try {
            $request->validate([
                'titulo' => 'required|string',
                'descricao' => 'sometimes|string|nullable',
                'status' => 'sometimes|string|in:pendente,concluida',
            ]);
            $usuario = Auth::user();
            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }
            $tarefa = $this->tarefaService->criarTarefa(
                $usuario->id_usuario,
                $id_meta,
                $request->input('titulo'),
                $request->input('descricao'),
                $request->input('status', 'pendente')
            );
            return ResponseHelper::success($tarefa, 'Tarefa criada dentro da meta', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao criar tarefa: ' . $e->getMessage(), 500);
        }
    }

    public function atualizarStatusTarefa(Request $request, int $id_meta, int $id_tarefa)
    {
        try {
            $request->validate([
                'status' => 'required|string|in:pendente,concluida',
            ]);
            $usuario = Auth::user();
            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }
            $tarefaAtualizada = $this->tarefaService->atualizarTarefa(
                $id_tarefa,
                ['status' => $request->input('status')],
                $usuario->id_usuario
            );
            return ResponseHelper::success($tarefaAtualizada, 'Status da tarefa atualizado', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao atualizar tarefa: ' . $e->getMessage(), 500);
        }
    }
}
