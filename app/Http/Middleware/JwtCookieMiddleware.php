<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class JwtCookieMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $token = $request->cookie('token');

            if (!$token) {
                return response()->json(['message' => 'Token ausente'], 401);
            }

            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json(['message' => 'Usuário não encontrado'], 401);
            }

            // Disponibiliza o usuário tanto via request quanto via Auth::user()
            $request->merge(['auth_user' => $user]);
            Auth::setUser($user);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token expirado'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token inválido'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token ausente ou inválido'], 401);
        }

        return $next($request);
    }
}
