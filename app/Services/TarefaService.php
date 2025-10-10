<?php

namespace App\Services;

use App\Models\TarefaModel;

class TarefaService
{
    public function criarTarefa(string $titulo, string $importancia, string $status, bool $ativo)
    {
        $tarefa = TarefaModel::create([
            'titulo' => $titulo,
            'importancia' => $importancia,
            'status' => $status,
            'ativo' => $ativo,
        ]);

        if (!in_array($tarefa->status, ['pendente', 'em_andamento', 'concluida'])) {
            throw new \Exception('Status inválido');
        }

        if (!in_array($tarefa->importancia, ['baixa', 'media', 'alta'])) {
            throw new \Exception('Importância inválida');
        }

        return $tarefa;
    }

    public function listarTarefas()
    {
        $tarefas = TarefaModel::all([
            'id_tarefa',
            'titulo',
            'importancia',
            'status',
            'ativo',
            'created_at'
        ]);

        return $tarefas;
    }

    public function atualizarTarefa(int $id, array $dados)
    {
        try {
            $tarefa = TarefaModel::find($id);
            if (!$tarefa) {
                throw new \Exception("Tarefa não encontrada");
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
}
