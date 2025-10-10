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
}
