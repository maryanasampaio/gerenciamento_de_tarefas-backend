<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Services\MetaService;
use App\Services\TarefaService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class MetaController extends Controller
{
    public function __construct(private MetaService $metaService, private TarefaService $tarefaService)
    {
    }

    private function getUsuarioFromToken(Request $request)
    {
        try {
            $token = $request->cookie('token') ?: JWTAuth::getToken();

            if (!$token) {
                return ResponseHelper::error('Token ausente', 401);
            }

            $usuario = JWTAuth::setToken($token)->authenticate();

            if (!$usuario) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }

            return $usuario;
        } catch (TokenExpiredException $e) {
            return ResponseHelper::error('Token expirado', 401);
        } catch (TokenInvalidException $e) {
            return ResponseHelper::error('Token inválido', 401);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao autenticar usuário: ' . $e->getMessage(), 401);
        }
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

            $usuario = $this->getUsuarioFromToken($request);
            if ($usuario instanceof \Illuminate\Http\JsonResponse) {
                return $usuario;
            }

            $dados = $request->all();
            \Log::info('[MetaController::criar] Dados recebidos:', $dados);
            \Log::info('[MetaController::criar] Tipo recebido:', ['tipo' => $dados['tipo'] ?? 'NULL']);

            $meta = $this->metaService->criarMeta($dados, $usuario->id_usuario);
            
            \Log::info('[MetaController::criar] Meta criada:', [
                'id' => $meta->id_meta,
                'tipo_salvo' => $meta->tipo,
                'titulo' => $meta->titulo
            ]);

            return ResponseHelper::success($meta, 'Meta criada com sucesso', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao criar meta: ' . $e->getMessage(), 500);
        }
    }

    public function resumo()
    {
        try {
            $fakeRequest = request();
            $usuario = $this->getUsuarioFromToken($fakeRequest);
            if ($usuario instanceof \Illuminate\Http\JsonResponse) {
                return $usuario;
            }

            $resumo = $this->metaService->resumoMetas($usuario->id_usuario);
            return ResponseHelper::success($resumo, 'Resumo de metas', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao obter resumo: ' . $e->getMessage(), 500);
        }
    }

    public function listar(Request $request)
    {
        try {
            $request->validate([
                'tipo' => 'sometimes|string|in:diaria,mensal,anual',
                'data' => 'sometimes|string',
                'status' => 'sometimes|string|in:pendente,em_andamento,concluida',
                'prioridade' => 'sometimes|string|in:baixa,media,alta',
                'pesquisa' => 'sometimes|string',
                'termo' => 'sometimes|string',
            ]);

            $usuario = $this->getUsuarioFromToken($request);
            if ($usuario instanceof \Illuminate\Http\JsonResponse) {
                return $usuario;
            }
            $tipo = $request->query('tipo');
            $data = $request->query('data'); // aceita d/m/Y ou Y-m-d
            $status = $request->query('status');
            $prioridade = $request->query('prioridade');
            $pesquisa = $request->query('pesquisa', $request->query('termo'));

            $resultado = $this->metaService->listarMetas(
                $usuario->id_usuario,
                $tipo,
                $data,
                $status,
                $prioridade,
                $pesquisa
            );

            return ResponseHelper::success($resultado, 'Metas listadas com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao listar metas: ' . $e->getMessage(), 500);
        }
    }

    public function detalhes(int $id)
    {
        try {
            $fakeRequest = request();
            $usuario = $this->getUsuarioFromToken($fakeRequest);
            if ($usuario instanceof \Illuminate\Http\JsonResponse) {
                return $usuario;
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
            $usuario = $this->getUsuarioFromToken($request);
            if ($usuario instanceof \Illuminate\Http\JsonResponse) {
                return $usuario;
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
            $fakeRequest = request();
            $usuario = $this->getUsuarioFromToken($fakeRequest);
            if ($usuario instanceof \Illuminate\Http\JsonResponse) {
                return $usuario;
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
            $usuario = $this->getUsuarioFromToken($request);
            if ($usuario instanceof \Illuminate\Http\JsonResponse) {
                return $usuario;
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
            $usuario = $this->getUsuarioFromToken($request);
            if ($usuario instanceof \Illuminate\Http\JsonResponse) {
                return $usuario;
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
