<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{

    protected $table = 'tb_usuario';
    protected $primary_key = 'id_usuario';
    public $timestamps = true;

    protected $fillable = [
        'usuario',
        'senha',
        'nome_completo',
        'email',
    ];

    protected $hidden = [
        'senha',
    ];

    protected $dates = [
        'deleted_at',
    ];
}
