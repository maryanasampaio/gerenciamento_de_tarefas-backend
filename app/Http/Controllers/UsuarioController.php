<?php

namespace App\Http\Controllers;

use App\Services\UsuarioService;;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    protected $usuarioService;

    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function criar(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'nome_completo' => 'required|string|max:255',
                'usuario' => 'required|string|max:50|unique:tb_usuario,usuario',
                'email' => 'required|email|unique:tb_usuario,email',
                'senha' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return ResponseHelper::error('Erro de validação', 422, $validator->errors());
            }

            $usuario = $this->usuarioService->criarUsuario(
                $request->input('nome_completo'),
                $request->input('usuario'),
                $request->input('email'),
                $request->input('senha')
            );

            return ResponseHelper::success($usuario, 'Usuário criado com sucesso', 201);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function listar()
    {
        try {
            $usuarios = $this->usuarioService->listarUsuarios();

            if (!$usuarios) {
                return ResponseHelper::error('Nenhum usuário encontrado', 404);
            }

            return ResponseHelper::success($usuarios, 'Usuários listados com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function atualizar(int $id_usuario, Request $request)
    {
        try {
            $request->validate([
                'nome_completo' => 'sometimes|string|max:255',
                'usuario' => 'sometimes|string|max:50',
                'email' => 'sometimes|email',
                'senha' => 'sometimes|string|min:8',
            ]);

            if (empty($id_usuario)) {
                return ResponseHelper::error('ID do usuário não informado', 400);
            }

            $usuario = $this->usuarioService->atualizarUsuario($id_usuario, $request->all());

            return ResponseHelper::success($usuario, 'Usuário atualizado com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }

    public function atualizarMe(Request $request)
    {
        try {
            $token = $request->cookie('token') ?? JWTAuth::getToken();
            if (!$token) {
                return ResponseHelper::error('Token ausente', 401);
            }

            $authUser = JWTAuth::setToken($token)->authenticate();
            if (!$authUser) {
                return ResponseHelper::error('Usuário não autenticado', 401);
            }

            $rules = [
                'nome' => 'required|string|max:255',
                'usuario' => 'required|string|max:50|unique:tb_usuario,usuario,' . $authUser->id_usuario . ',id_usuario',
                'email' => 'required|email|unique:tb_usuario,email,' . $authUser->id_usuario . ',id_usuario',
            ];

            if ($request->filled('senha_atual') || $request->filled('nova_senha')) {
                $rules['senha_atual'] = 'required|string';
                $rules['nova_senha'] = 'required|string|min:8|confirmed';
            }

            $validator = validator($request->all(), $rules);
            if ($validator->fails()) {
                return ResponseHelper::error('Erro de validação', 422, $validator->errors());
            }

            $dados = [
                'nome_completo' => $request->input('nome'),
                'usuario' => $request->input('usuario'),
                'email' => $request->input('email'),
            ];

            if ($request->filled('nova_senha')) {
                if (!Hash::check($request->input('senha_atual', ''), $authUser->senha)) {
                    return ResponseHelper::error('Senha atual inválida', 401);
                }
                $dados['senha'] = $request->input('nova_senha');
            }

            $atualizado = $this->usuarioService->atualizarUsuario($authUser->id_usuario, $dados);

            unset($atualizado['senha']);

            return ResponseHelper::success($atualizado, 'Perfil atualizado com sucesso', 200);
        } catch (\Exception $e) {
            return ResponseHelper::error($e->getMessage(), 500);
        }
    }
}
