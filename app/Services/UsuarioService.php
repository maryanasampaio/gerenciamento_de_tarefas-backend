<?php

namespace App\Services;

use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;


class UsuarioService
{


    public function criarUsuario(String $nome, String $usuario, String $email, String $senha)
    {
        try {

            $usuarioExiste =  Usuario::where('usuario', $usuario)->first();

            if ($usuarioExiste) {
                throw new \Exception('Usuário já existe');
            }

            $novoUsuario = Usuario::create(
                [
                    'nome_completo' => $nome,
                    'usuario' =>
                    $usuario,
                    'email' =>
                    $email,
                    'senha' => Hash::make($senha),

                ]
            );

            return [
                'id_usuario' => $novoUsuario->id,
                'nome_completo' => $novoUsuario->nome_completo,
                'usuario' => $novoUsuario->usuario,
                'email' => $novoUsuario->email,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erro ao criar usuário: ' . $e->getMessage());
        }
    }

    public function listarUsuarios()
    {

        $usuarios = Usuario::all();
        return $usuarios;
    }




    public function atualizarUsuario($id, array $dados)
    {
        try {
            $usuario = Usuario::where('id_usuario', $id)->first();

            if (!$usuario) {
                throw new \Exception('Usuário não encontrado');
            }

            // Atualiza apenas os campos enviados
            if (isset($dados['nome_completo'])) {
                $usuario->nome_completo = $dados['nome_completo'];
            }
            if (isset($dados['usuario'])) {
                $usuario->usuario = $dados['usuario'];
            }
            if (isset($dados['email'])) {
                $usuario->email = $dados['email'];
            }
            if (!empty($dados['senha'])) {
                $usuario->senha = Hash::make($dados['senha']);
            }

            $usuario->save();

            return [
                'id_usuario' => $usuario->id_usuario,
                'nome_completo' => $usuario->nome_completo,
                'usuario' => $usuario->usuario,
                'email' => $usuario->email,
                'senha' => $usuario->senha,
            ];
        } catch (\Exception $e) {
            throw new \Exception('Erro interno ao atualizar usuário: ' . $e->getMessage());
        }
    }
}
