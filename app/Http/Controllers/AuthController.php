<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use Illuminate\Http\Request;
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

            $usuario = $this->authService->login(
                $request->input('usuario'),
                $request->input('senha')
            );

            return response()
                ->json([
                    'message' => 'Login realizado com sucesso',
                    'usuario' => $usuario['usuario']
                ], 200)
                ->cookie(
                    'token',
                    $usuario['token'],
                    60,
                    '/',
                    null,
                    false,
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
            $cookie = $this->authService->logout();
            return ResponseHelper::success(null, 'Logout realizado com sucesso', 200)
                ->withCookie($cookie);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
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
