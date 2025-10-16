<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Services\AuthService;
use Illuminate\Http\Request;

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
            //validação de campos obrigatórios para o login
            $request->validate([
                'usuario' => 'required|string',
                'senha' => 'required|string',
            ]);

            //envio dos dados para a validação e busca no banco de dados
            $usuario = $this->authService->login(
                $request->input('usuario'),
                $request->input('senha')
            );
            //validar e tratar respostas em casos de 400, 200 e 500

            //o usuário existe na base? erro 400
            if (!$usuario) {
                return ResponseHelper::error('Credenciais inválidas', 401);
            }
            //caso o usuário exista: 200
            return ResponseHelper::success($usuario, 'Login realizado com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error('Erro interno: ' . $e->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->authService->logout();
            return ResponseHelper::success(null, 'Logout realizado com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
