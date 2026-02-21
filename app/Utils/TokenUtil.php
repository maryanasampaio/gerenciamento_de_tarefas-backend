<?php

namespace App\Utils;

use App\Models\Usuario;
use App\Models\RefreshToken;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TokenUtil
{
    public static function generateAccessToken(Usuario $usuario): string
    {
        return JWTAuth::fromUser($usuario);
    }

    public static function createRefreshToken(Usuario $usuario): string
    {
        $token = Str::random(64);
        $expiresAt = Carbon::now()->addDays(30);

        RefreshToken::create([
            'id_usuario' => $usuario->id_usuario,
            'token' => hash('sha256', $token),
            'expires_at' => $expiresAt,
        ]);

        return $token;
    }

    public static function findValidRefreshToken(string $token): ?RefreshToken
    {
        $hashed = hash('sha256', $token);
        $rt = RefreshToken::where('token', $hashed)->first();
        if (!$rt) {
            return null;
        }
        return $rt->isValid() ? $rt : null;
    }

    public static function rotateRefreshToken(RefreshToken $oldToken, Usuario $usuario): string
    {
        $oldToken->revoke();
        return self::createRefreshToken($usuario);
    }

    public static function revokeUserTokens(int $idUsuario): void
    {
        RefreshToken::where('id_usuario', $idUsuario)
            ->where('revoked', false)
            ->update(['revoked' => true]);
    }
}
