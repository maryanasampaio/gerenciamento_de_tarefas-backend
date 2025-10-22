<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

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

            return ResponseHelper::success(
                $resultado['usuario'],
                'Login realizado com sucesso',
                200
            )->withCookie($resultado['cookie']);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao fazer login: ' . $e->getMessage(), 500);
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

    public function usuarioAutenticado()
    {
        try {
            $usuario = JWTAuth::parseToken()->authenticate();

            if (!$usuario) {
                return ResponseHelper::error('Usuário não encontrado', 404);
            }

            return ResponseHelper::success([
                'id_usuario' => $usuario->id_usuario,
                'nome_completo' => $usuario->nome_completo,
                'usuario' => $usuario->usuario,
                'email' => $usuario->email,
            ], 'Usuário autenticado obtido com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro ao obter usuário autenticado: ' . $e->getMessage(), 500);
        }
    }
}
