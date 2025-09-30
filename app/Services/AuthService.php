<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;




class AuthService
{

    public function login($usuario, $senha)
    {

        $usuario = Usuario::where('usuario', $usuario)->first();

        if (!$usuario) {
            throw new \Exception('Usuário não encontrado');
        }
        if (!Hash::check($senha, $usuario->senha)) {
            throw new \Exception('Senha incorreta');
        }
        $token = JWTAuth::fromUser($usuario);

        return [
            'usuario' => [
                'nome'         => $usuario->nome_completo,
                'usuario'     => $usuario->usuario,
                'email'        => $usuario->email,
            ],
            'token' => $token,

        ];
    }
}
