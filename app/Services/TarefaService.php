<?php

namespace App\Services;

use App\Models\TarefaModel;

class TarefaService
{
    public function criarTarefa(string $titulo, string $importancia, string $status, bool $ativo, $id_usuario)
    {
        $tarefa = TarefaModel::create([
            'titulo' => $titulo,
            'importancia' => $importancia,
            'status' => $status,
            'ativo' => $ativo,
            'id_usuario' => $id_usuario
        ]);

        if (!in_array($tarefa->status, ['pendente', 'em_andamento', 'concluida'])) {
            throw new \Exception('Status inválido');
        }

        if (!in_array($tarefa->importancia, ['baixa', 'media', 'alta'])) {
            throw new \Exception('Importância inválida');
        }

        return $tarefa;
    }

    public function listarTarefas(int $id_usuario)
    {
        try {
            $tarefas = TarefaModel::where('id_usuario', $id_usuario)
                ->orderBy('created_at', 'desc')
                ->get([
                    'id_tarefa',
                    'titulo',
                    'importancia',
                    'status',
                    'ativo',
                    'created_at',
                    'id_usuario'
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

            if (isset($dados['status']) && !in_array($dados['status'], ['pendente', 'em_andamento', 'concluida'])) {
                throw new \Exception('Status inválido');
            }

            if (isset($dados['importancia']) && !in_array($dados['importancia'], ['baixa', 'media', 'alta'])) {
                throw new \Exception('Importância inválida');
            }

            $tarefa->fill($dados);
            $tarefa->save();

            return [
                'id_tarefa' => $tarefa->id_tarefa,
                'titulo' => $tarefa->titulo,
                'importancia' => $tarefa->importancia,
                'status' => $tarefa->status,
                'ativo' => $tarefa->ativo,
                'created_at' => $tarefa->created_at,
                'updated_at' => $tarefa->updated_at,
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
}
