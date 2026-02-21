<?php

namespace App\Services;

use App\Models\Meta;
use App\Models\TarefaModel;
use Carbon\Carbon;

class MetaService
{
    public function criarMeta(array $dados, int $id_usuario): Meta
    {
        \Log::info('[MetaService::criarMeta] Recebido:', ['tipo' => $dados['tipo'] ?? 'NULL', 'dados' => $dados]);

        if (!in_array($dados['tipo'] ?? '', ['diaria', 'mensal', 'anual'])) {
            throw new \Exception('Tipo de meta inválido');
        }

        // Para metas mensais e anuais: se não informar datas, usar período atual
        if (in_array($dados['tipo'], ['mensal', 'anual'])) {
            $inicio = $this->normalizeDate($dados['data_inicio'] ?? null);
            $fim = $this->normalizeDate($dados['data_fim'] ?? null);

            \Log::info('[MetaService::criarMeta] Datas normalizadas:', [
                'tipo' => $dados['tipo'],
                'data_inicio_original' => $dados['data_inicio'] ?? 'null',
                'data_fim_original' => $dados['data_fim'] ?? 'null',
                'inicio_normalizado' => $inicio,
                'fim_normalizado' => $fim
            ]);

            // Se não informou as datas, são inválidas, ou data_fim <= data_inicio → gerar automaticamente
            if (!$inicio || !$fim || $inicio >= $fim) {
                if ($dados['tipo'] === 'mensal') {
                    $inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
                    $fim = Carbon::now()->endOfMonth()->format('Y-m-d');
                } else { // anual
                    $inicio = Carbon::now()->startOfYear()->format('Y-m-d');
                    $fim = Carbon::now()->endOfYear()->format('Y-m-d');
                }
                
                \Log::info('[MetaService::criarMeta] Datas geradas automaticamente:', [
                    'tipo' => $dados['tipo'],
                    'inicio' => $inicio,
                    'fim' => $fim
                ]);
            }

            $dados['data_inicio'] = $inicio;
            $dados['data_fim'] = $fim;
        }
        // Para metas diárias, ignorar datas se enviadas
        if ($dados['tipo'] === 'diaria') {
            $dados['data_inicio'] = null;
            $dados['data_fim'] = null;
        }

        $dadosParaSalvar = [
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
        ];

        \Log::info('[MetaService::criarMeta] Dados para salvar:', $dadosParaSalvar);

        $meta = Meta::create($dadosParaSalvar);

        \Log::info('[MetaService::criarMeta] Meta salva:', [
            'id' => $meta->id_meta,
            'tipo' => $meta->tipo,
            'tipo_original' => $dados['tipo']
        ]);

        return $meta;
    }

    public function resumoMetas(int $id_usuario): array
    {
        // Recalcula status de todas as metas do usuário antes de contar
        $todasMetas = Meta::where('id_usuario', $id_usuario)->get();
        foreach ($todasMetas as $meta) {
            $this->sincronizarStatusMeta($meta);
        }

        $pendentes = Meta::where('id_usuario', $id_usuario)->where('status', 'pendente')->count();
        $emAndamento = Meta::where('id_usuario', $id_usuario)->where('status', 'em_andamento')->count();
        $concluidas = Meta::where('id_usuario', $id_usuario)->where('status', 'concluida')->count();

        return [
            'pendentes' => $pendentes,
            'em_andamento' => $emAndamento,
            'concluidas' => $concluidas,
            'total' => $pendentes + $emAndamento + $concluidas,
        ];
    }

    public function listarMetas(
        int $id_usuario,
        ?string $tipo = null,
        ?string $data = null,
        ?string $status = null,
        ?string $prioridade = null,
        ?string $pesquisa = null
    )
    {
        \Log::info('[MetaService::listarMetas] Chamado com:', [
            'id_usuario' => $id_usuario,
            'tipo' => $tipo,
            'data' => $data,
            'status' => $status,
            'prioridade' => $prioridade,
            'pesquisa' => $pesquisa
        ]);

        $query = Meta::where('id_usuario', $id_usuario)->orderBy('created_at', 'desc');

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        if ($prioridade) {
            $query->where('prioridade', $prioridade);
        }

        if (!empty($pesquisa)) {
            $query->where(function ($q) use ($pesquisa) {
                $q->where('titulo', 'like', "%{$pesquisa}%")
                    ->orWhere('descricao', 'like', "%{$pesquisa}%")
                    ->orWhere('contexto', 'like', "%{$pesquisa}%")
                    ->orWhere('tipo', 'like', "%{$pesquisa}%")
                    ->orWhere('prioridade', 'like', "%{$pesquisa}%")
                    ->orWhere('status', 'like', "%{$pesquisa}%");
            });
        }

        // Normalizar a data recebida (d/m/Y ou Y-m-d). Se tipo=diaria e data não vier, usa hoje.
        $dateFilter = $this->normalizeDate($data);

        if ($tipo === 'diaria') {
            $dateFilter = $dateFilter ?: \Carbon\Carbon::now()->format('Y-m-d');
            $query->whereDate('created_at', $dateFilter);
        } elseif ($dateFilter && in_array($tipo, ['mensal', 'anual'])) {
            $query->where('data_inicio', '<=', $dateFilter)->where('data_fim', '>=', $dateFilter);
        } elseif ($dateFilter && !$tipo) {
            // Sem tipo: combinar filtros — diárias no dia e mensais/anuais que contem o dia
            $query->where(function ($q) use ($dateFilter) {
                $q->where(function ($q2) use ($dateFilter) {
                    $q2->where('tipo', 'diaria')->whereDate('created_at', $dateFilter);
                })->orWhere(function ($q3) use ($dateFilter) {
                    $q3->whereIn('tipo', ['mensal', 'anual'])
                        ->where('data_inicio', '<=', $dateFilter)
                        ->where('data_fim', '>=', $dateFilter);
                });
            });
        }

        \Log::info('[MetaService::listarMetas] SQL Query:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $metas = $query->get()->map(function (Meta $meta) {
            $this->sincronizarStatusMeta($meta);
            $progresso = $this->calcularProgresso($meta->id_meta);
            return array_merge($meta->toArray(), ['progresso' => $progresso]);
        });

        \Log::info('[MetaService::listarMetas] Metas encontradas antes do filtro de status:', [
            'count' => $metas->count(),
            'ids' => $metas->pluck('id_meta')->toArray()
        ]);

        if ($status) {
            $metas = $metas->filter(function (array $meta) use ($status) {
                return ($meta['status'] ?? null) === $status;
            })->values();
            
            \Log::info('[MetaService::listarMetas] Metas após filtro de status:', [
                'status_filtrado' => $status,
                'count' => $metas->count()
            ]);
        }

        // Calcular resumo após aplicar filtros
        $resumo = [
            'pendentes' => $metas->where('status', 'pendente')->count(),
            'em_andamento' => $metas->where('status', 'em_andamento')->count(),
            'concluidas' => $metas->where('status', 'concluida')->count(),
            'total' => $metas->count(),
        ];

        return [
            'metas' => $metas->values()->all(),
            'resumo' => $resumo,
        ];
    }

    private function sincronizarStatusMeta(Meta $meta): void
    {
        $progresso = $this->calcularProgresso($meta->id_meta);
        $statusCalculado = $this->resolverStatusPorProgresso($progresso['total'], $progresso['concluidas']);

        if ($meta->status !== $statusCalculado) {
            $meta->status = $statusCalculado;
            $meta->save();
        }
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
        $novoStatus = $this->resolverStatusPorProgresso($total, $concluidas);

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

        // Rejeitar valores incompletos (só ano "2026" ou mês/ano "02/2026")
        // Aceitar apenas datas completas
        if (preg_match('/^\d{4}$/', $value) || preg_match('/^\d{2}\/\d{4}$/', $value)) {
            return null; // Tratar como não informado para gerar automaticamente
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

    private function resolverStatusPorProgresso(int $total, int $concluidas): string
    {
        if ($total <= 0) {
            return 'pendente';
        }

        if ($concluidas === $total) {
            return 'concluida';
        }

        if ($concluidas > 0) {
            return 'em_andamento';
        }

        return 'pendente';
    }
}
