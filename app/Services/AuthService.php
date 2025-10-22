<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cookie;

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


        $cookie = cookie(
            'token',
            $token,
            config('jwt.ttl'),
            '/',
            null,
            false,
            true,
            false,
            'Strict'
        );


        return [
            'usuario' => [
                'id_usuario'   => $usuario->id_usuario,
                'nome'         => $usuario->nome_completo,
                'usuario'      => $usuario->usuario,
                'email'        => $usuario->email,
            ],
            'cookie' => $cookie
        ];
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            if ($token) {
                JWTAuth::invalidate($token);
            }


            $cookie = Cookie::forget('token');

            return $cookie;
        } catch (\Exception $e) {
            throw new \Exception('Erro ao realizar logout: ' . $e->getMessage());
        }
    }
}
