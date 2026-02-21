<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $table = 'tb_meta';
    protected $primaryKey = 'id_meta';
    public $timestamps = true;
    protected $softDeletes = true;

    protected $fillable = [
        'titulo',
        'descricao',
        'contexto',
        'prioridade',
        'status',
        'tipo',
        'data_inicio',
        'data_fim',
        'ativo',
        'id_usuario'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function tarefas()
    {
        return $this->hasMany(TarefaModel::class, 'id_meta', 'id_meta');
    }
}
