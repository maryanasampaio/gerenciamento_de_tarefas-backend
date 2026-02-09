<?php

namespace App\Services;

use App\Models\Meta;
use App\Models\TarefaModel;
use Carbon\Carbon;

class MetaService
{
    public function criarMeta(array $dados, int $id_usuario): Meta
    {
        if (!in_array($dados['tipo'] ?? '', ['diaria', 'mensal', 'anual'])) {
            throw new \Exception('Tipo de meta inválido');
        }

        // Datas obrigatórias para mensal e anual
        if (in_array($dados['tipo'], ['mensal', 'anual'])) {
            $inicio = $this->normalizeDate($dados['data_inicio'] ?? null);
            $fim = $this->normalizeDate($dados['data_fim'] ?? null);
            if (!$inicio || !$fim) {
                throw new \Exception('data_inicio e data_fim inválidas. Use formatos d/m/Y ou Y-m-d.');
            }
            $dados['data_inicio'] = $inicio;
            $dados['data_fim'] = $fim;
        }
        // Para metas diárias, ignorar datas se enviadas
        if ($dados['tipo'] === 'diaria') {
            $dados['data_inicio'] = null;
            $dados['data_fim'] = null;
        }

        $meta = Meta::create([
            'titulo' => $dados['titulo'],
            'descricao' => $dados['descricao'] ?? null,
            'contexto' => $dados['contexto'] ?? null,
            'prioridade' => $dados['prioridade'] ?? 'baixa',
            'status' => 'pendente',
            'tipo' => $dados['tipo'],
            'data_inicio' => $dados['data_inicio'] ?? null,
            'data_fim' => $dados['data_fim'] ?? null,
            'ativo' => true,
            'id_usuario' => $id_usuario,
        ]);

        return $meta;
    }

    public function listarMetas(int $id_usuario, ?string $tipo = null)
    {
        $query = Meta::where('id_usuario', $id_usuario)->orderBy('created_at', 'desc');
        if ($tipo) {
            $query->where('tipo', $tipo);
        }
        $metas = $query->get();
        return $metas->map(function (Meta $meta) {
            $progresso = $this->calcularProgresso($meta->id_meta);
            return array_merge($meta->toArray(), ['progresso' => $progresso]);
        });
    }

    public function detalhesMeta(int $id_meta, int $id_usuario)
    {
        $meta = Meta::where('id_meta', $id_meta)->where('id_usuario', $id_usuario)->first();
        if (!$meta) {
            throw new \Exception('Meta não encontrada ou não pertence ao usuário.');
        }
        $tarefas = TarefaModel::where('id_meta', $id_meta)->orderBy('created_at', 'desc')->get();
        $progresso = $this->calcularProgresso($id_meta);
        return [
            'meta' => $meta,
            'tarefas' => $tarefas,
            'progresso' => $progresso,
        ];
    }

    public function atualizarMeta(int $id_meta, array $dados, int $id_usuario)
    {
        $meta = Meta::where('id_meta', $id_meta)->where('id_usuario', $id_usuario)->first();
        if (!$meta) {
            throw new \Exception('Meta não encontrada ou não pertence ao usuário.');
        }

        if (isset($dados['tipo']) && !in_array($dados['tipo'], ['diaria', 'mensal', 'anual'])) {
            throw new \Exception('Tipo de meta inválido');
        }
        if (isset($dados['prioridade']) && !in_array($dados['prioridade'], ['baixa', 'media', 'alta'])) {
            throw new \Exception('Prioridade inválida');
        }

        // Normalizar datas quando enviadas
        if (array_key_exists('data_inicio', $dados)) {
            $dados['data_inicio'] = $this->normalizeDate($dados['data_inicio']);
        }
        if (array_key_exists('data_fim', $dados)) {
            $dados['data_fim'] = $this->normalizeDate($dados['data_fim']);
        }
        // Se tipo mudar para diaria, limpar datas
        if (($dados['tipo'] ?? $meta->tipo) === 'diaria') {
            $dados['data_inicio'] = null;
            $dados['data_fim'] = null;
        }

        $meta->fill($dados);
        $meta->save();

        // status é auto-gerenciado pelas tarefas
        $this->atualizarStatusMetaPorTarefas($id_meta);

        $progresso = $this->calcularProgresso($id_meta);
        return array_merge($meta->toArray(), ['progresso' => $progresso]);
    }

    public function deletarMeta(int $id_meta, int $id_usuario)
    {
        $meta = Meta::where('id_meta', $id_meta)->where('id_usuario', $id_usuario)->first();
        if (!$meta) {
            throw new \Exception('Meta não encontrada ou não pertence ao usuário.');
        }
        $meta->delete();
        return true;
    }

    public function calcularProgresso(int $id_meta): array
    {
        $total = TarefaModel::where('id_meta', $id_meta)->count();
        $concluidas = TarefaModel::where('id_meta', $id_meta)->where('status', 'concluida')->count();
        $percentual = $total > 0 ? round(($concluidas / $total) * 100) : 0;
        return [
            'total' => $total,
            'concluidas' => $concluidas,
            'percentual' => $percentual,
        ];
    }

    public function atualizarStatusMetaPorTarefas(int $id_meta): void
    {
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
        $meta = Meta::find($id_meta);
        if ($meta && $meta->status !== $novoStatus) {
            $meta->status = $novoStatus;
            $meta->save();
        }
    }

    private function normalizeDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        // Tentar d/m/Y
        try {
            $dt = Carbon::createFromFormat('d/m/Y', $value);
            return $dt->format('Y-m-d');
        } catch (\Exception $e) {
        }
        // Tentar Y-m-d ou parsing genérico
        try {
            $dt = Carbon::parse($value);
            return $dt->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
