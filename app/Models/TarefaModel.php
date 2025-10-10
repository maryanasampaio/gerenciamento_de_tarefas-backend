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
        'importancia',
        'status',
        'ativo',
    ];
}
