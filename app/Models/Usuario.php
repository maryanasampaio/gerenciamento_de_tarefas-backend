<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Usuario extends Authenticatable implements JWTSubject
{
    protected $table = 'tb_usuario';
    protected $primaryKey = 'id_usuario';

    protected $fillable = [
        'nome_completo',
        'usuario',
        'email',
        'senha'
    ];


    public function getAuthPassword()
    {
        return $this->senha;
    }


    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function tarefas()
    {
        return $this->hasMany(TarefaModel::class, 'id_usuario', 'id_usuario');
    }
}
