<?php

namespace App\Http\Controllers;

use App\Services\UsuarioService;;

use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;

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
}
