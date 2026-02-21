<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\RefreshToken;
use App\Utils\TokenUtil;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthService
{
    private function accessTokenExpiresAt(): string
    {
        $ttlMinutes = (int) config('jwt.ttl', 60);
        return Carbon::now()->addMinutes($ttlMinutes)->toIso8601String();
    }

    private function refreshTokenExpiresAt(): string
    {
        $refreshTtlMinutes = (int) config('jwt.refresh_ttl', 43200);
        return Carbon::now()->addMinutes($refreshTtlMinutes)->toIso8601String();
    }

    public function login(string $usuario, string $senha): array
    {
        $usuario = Usuario::where('usuario', $usuario)->first();

        if (!$usuario) {
            throw new \Exception("Usuário não encontrado");
        }

        if (!Hash::check($senha, $usuario->senha)) {
            throw new \Exception("Senha inválida");
        }

        $accessToken = TokenUtil::generateAccessToken($usuario);
        $refreshToken = TokenUtil::createRefreshToken($usuario);

        return [
            'usuario' => [
                'nome' => $usuario->nome_completo,
                'usuario' => $usuario->usuario,
                'email' => $usuario->email,
            ],
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => $this->accessTokenExpiresAt(),
            'refresh_expires_at' => $this->refreshTokenExpiresAt(),
        ];
    }

    public function refresh(string $refreshTokenString): array
    {
        $refreshToken = TokenUtil::findValidRefreshToken($refreshTokenString);

        if (!$refreshToken) {
            throw new \Exception('Refresh token inválido ou expirado');
        }

        // Busca o usuário
        $usuario = Usuario::find($refreshToken->id_usuario);

        if (!$usuario) {
            throw new \Exception('Usuário não encontrado');
        }

        $accessToken = TokenUtil::generateAccessToken($usuario);
        $newRefreshToken = TokenUtil::rotateRefreshToken($refreshToken, $usuario);

        return [
            'usuario' => [
                'nome' => $usuario->nome_completo,
                'usuario' => $usuario->usuario,
                'email' => $usuario->email,
            ],
            'access_token' => $accessToken,
            'refresh_token' => $newRefreshToken,
            'expires_at' => $this->accessTokenExpiresAt(),
            'refresh_expires_at' => $this->refreshTokenExpiresAt(),
        ];
    }

    public function logout()
    {
        $token = JWTAuth::getToken() ?: request()->cookie('token');
        if (!$token) {
            throw new \Exception('Token não encontrado');
        }
        
        JWTAuth::setToken($token)->invalidate();
        $usuario = JWTAuth::setToken($token)->authenticate();
        if ($usuario) {
            TokenUtil::revokeUserTokens($usuario->id_usuario);
        }
        
        Cookie::queue(Cookie::forget('token'));
        Cookie::queue(Cookie::forget('refresh_token'));
        
        return true;
    }

    private function createRefreshToken(Usuario $usuario): string
    {
        return TokenUtil::createRefreshToken($usuario);
    }
}
