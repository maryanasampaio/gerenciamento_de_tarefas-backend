<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function login(string $usuario, string $senha): array
    {
        $usuario = Usuario::where('usuario', $usuario)->first();

        if (!$usuario) {
            throw new \Exception("Usuário não encontrado");
        }

        if (!Hash::check($senha, $usuario->senha)) {
            throw new \Exception("Senha inválida");
        }

        $token = JWTAuth::fromUser($usuario);

        return [
            'usuario' => [
                'nome' => $usuario->nome_completo,
                'usuario' => $usuario->usuario,
                'email' => $usuario->email,
            ],
            'token' => $token

        ];
    }


    public function logout()
    {
        $token = JWTAuth::getToken() ?: request()->cookie('token'); // tenta do header e do cookie
        if (!$token) {
            throw new \Exception('Token não encontrado');
        }
        JWTAuth::setToken($token)->invalidate();
        Cookie::queue(Cookie::forget('token'));
        return true;
    }
}
