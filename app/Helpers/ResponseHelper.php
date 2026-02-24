<?php

namespace App\Helpers;

class ResponseHelper
{

    public static function success($data = [], $mensagem = 'Operação realizada com sucesso', $status = 200)
    {
        return response()->json([
            'status' => $status,
            'mensagem' => $mensagem,
            'dados' => $data
        ], $status);
    }


    public static function error($mensagem = 'Ocorreu um erro', $status = 400, $data = [])
    {
        return response()->json([
            'status' => $status,
            'mensagem' => $mensagem,
            'dados' => $data
        ], $status);
    }
}
