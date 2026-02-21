<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarefaModel extends Model
{
    protected $table = 'tb_tarefa';
    protected $primaryKey = 'id_tarefa';
    public  $timestamps = true;
    protected $softDeletes = true;

    protected $fillable = [

        'titulo',
        'descricao',
        'status',
        'ativo',
        'id_usuario',
        'id_meta'
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function meta()
    {
        return $this->belongsTo(Meta::class, 'id_meta', 'id_meta');
    }
}
