<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;



class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'usuario' => 'required|string',
                'senha' => 'required|string',
            ]);

            $resultado = $this->authService->login(
                $request->input('usuario'),
                $request->input('senha')
            );

            return response()
                ->json([
                    'message' => 'Login realizado com sucesso',
                    'usuario' => $resultado['usuario'],
                    'access_token' => $resultado['access_token'],
                ], 200)
                ->cookie(
                    'token',
                    $resultado['access_token'],
                    60,
                    '/',
                    null,
                    app()->environment('production'),
                    true,
                    false,
                    'Lax'
                )
                ->cookie(
                    'refresh_token',
                    $resultado['refresh_token'],
                    43200,
                    '/',
                    null,
                    app()->environment('production'),
                    true,
                    false,
                    'Lax'
                );
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function logout()
    {
        try {
            $this->authService->logout();
            
            return ResponseHelper::success(null, 'Logout realizado com sucesso', 200)
                ->withCookie(Cookie::forget('token'))
                ->withCookie(Cookie::forget('refresh_token'));
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function refresh(Request $request)
    {
        try {
            $refreshToken = $request->cookie('refresh_token') 
                ?? $request->input('refresh_token');

            if (!$refreshToken) {
                return ResponseHelper::error('Refresh token não fornecido', 400);
            }

            $resultado = $this->authService->refresh($refreshToken);

            return response()
                ->json([
                    'message' => 'Token renovado com sucesso',
                    'usuario' => $resultado['usuario'],
                    'access_token' => $resultado['access_token'],
                ], 200)
                ->cookie(
                    'token',
                    $resultado['access_token'],
                    60,
                    '/',
                    null,
                    app()->environment('production'),
                    true,
                    false,
                    'Lax'
                )
                ->cookie(
                    'refresh_token',
                    $resultado['refresh_token'],
                    43200,
                    '/',
                    null,
                    app()->environment('production'),
                    true,
                    false,
                    'Lax'
                );
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 401);
        }
    }

    public function usuarioAutenticado(Request $request)
    {
        try {
            $token = $request->cookie('token');
            if (!$token) {
                return ResponseHelper::error('Token ausente', 401);
            }

            $usuario = JWTAuth::setToken($token)->authenticate();

            if (!$usuario) {
                return ResponseHelper::error('Usuário não encontrado', 404);
            }

            return ResponseHelper::success([
                'id_usuario'    => $usuario->id_usuario,
                'nome_completo' => $usuario->nome_completo,
                'usuario'       => $usuario->usuario,
                'email'         => $usuario->email,
            ], 'Usuário autenticado obtido com sucesso', 200);
        } catch (TokenExpiredException $e) {
            return ResponseHelper::error('Token expirado', 401);
        } catch (TokenInvalidException $e) {
            return ResponseHelper::error('Token inválido', 401);
        } catch (JWTException $e) {
            return ResponseHelper::error('Erro no token: ' . $e->getMessage(), 401);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao obter usuário autenticado: ' . $e->getMessage(), 500);
        }
    }
}
