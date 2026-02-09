<?php

namespace App\Services;

use App\Models\TarefaModel;

class TarefaService
{
    public function criarTarefa(int $id_usuario, int $id_meta, string $titulo, ?string $descricao = null, string $status = 'pendente')
    {
        if (!in_array($status, ['pendente', 'concluida'])) {
            throw new \Exception('Status inválido');
        }

        $tarefa = TarefaModel::create([
            'titulo' => $titulo,
            'descricao' => $descricao,
            'status' => $status,
            'ativo' => true,
            'id_usuario' => $id_usuario,
            'id_meta' => $id_meta,
        ]);

        $this->atualizarStatusMetaPorTarefas($id_meta);

        return $tarefa;
    }

    public function listarTarefas(int $id_usuario, ?int $id_meta = null)
    {
        try {
            $query = TarefaModel::where('id_usuario', $id_usuario);
            if ($id_meta) {
                $query->where('id_meta', $id_meta);
            }

            $tarefas = $query
                ->orderBy('created_at', 'desc')
                ->get([
                    'id_tarefa',
                    'titulo',
                    'descricao',
                    'status',
                    'ativo',
                    'created_at',
                    'id_usuario',
                    'id_meta'
                ]);

            if ($tarefas->isEmpty()) {
                return [];
            }

            return $tarefas;
        } catch (\Exception $e) {
            throw new \Exception('Erro ao listar tarefas: ' . $e->getMessage());
        }
    }


    public function atualizarTarefa(int $id_tarefa, array $dados, int $id_usuario)
    {
        try {
            $tarefa = TarefaModel::where('id_tarefa', $id_tarefa)
                ->where('id_usuario', $id_usuario)
                ->first();

            if (!$tarefa) {
                throw new \Exception('Tarefa não encontrada ou não pertence ao usuário.');
            }

            if (isset($dados['status']) && !in_array($dados['status'], ['pendente', 'concluida'])) {
                throw new \Exception('Status inválido');
            }

            $tarefa->fill($dados);
            $tarefa->save();

            if ($tarefa->id_meta) {
                $this->atualizarStatusMetaPorTarefas($tarefa->id_meta);
            }

            return [
                'id_tarefa' => $tarefa->id_tarefa,
                'titulo' => $tarefa->titulo,
                'descricao' => $tarefa->descricao,
                'status' => $tarefa->status,
                'ativo' => $tarefa->ativo,
                'created_at' => $tarefa->created_at,
                'updated_at' => $tarefa->updated_at,
                'id_meta' => $tarefa->id_meta,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erro ao atualizar tarefa: ' . $e->getMessage());
        }
    }

    public function deletarTarefa(int $id_tarefa)
    {
        try {
            $tarefa = TarefaModel::find($id_tarefa);

            if (!$tarefa) {
                throw new \Exception("Tarefa não encontrada");
            }
            $tarefa->delete();
            return $tarefa;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function pesquisar(?string $termo)
    {
        try {
            if (empty($termo)) {
                throw new \Exception('Termo de pesquisa não informado');
            }

            $resultadoPesquisa = TarefaModel::where('titulo', 'like', "%{$termo}%")
                ->orWhere('status', 'like', "%{$termo}%")
                ->orderBy('created_at', 'desc')
                ->get();


            return $resultadoPesquisa;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    private function atualizarStatusMetaPorTarefas(int $id_meta): void
    {
        // Calcula o status da meta com base nas tarefas
        $total = TarefaModel::where('id_meta', $id_meta)->count();
        $concluidas = TarefaModel::where('id_meta', $id_meta)->where('status', 'concluida')->count();

        $novoStatus = 'pendente';
        if ($total > 0) {
            if ($concluidas === $total) {
                $novoStatus = 'concluida';
            } elseif ($concluidas > 0) {
                $novoStatus = 'em_andamento';
            }
        }

        $meta = \App\Models\Meta::find($id_meta);
        if ($meta && $meta->status !== $novoStatus) {
            $meta->status = $novoStatus;
            $meta->save();
        }
    }
}
