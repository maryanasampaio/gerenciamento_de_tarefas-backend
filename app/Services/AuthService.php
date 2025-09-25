<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;



class AuthService
{

    public function login($usuario, $senha): Usuario
    {

        $usuario = Usuario::where('usuario', $usuario)->first();
        // ex: select * from tb_usuario where usuario = 'adm'

        if (!$usuario) {
            throw new \Exception('Usuário não encontrado');
        }
        if (!Hash::check($senha, $usuario->senha)) {
            throw new \Exception('Senha incorreta');
        }
        return $usuario;
    }
}
