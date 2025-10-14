<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Define o "guard" e "password broker" padrão da aplicação.
    | Como estamos usando JWT, o guard padrão será o 'api'.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Aqui definimos todos os guards da aplicação.
    | O 'web' usa sessão, e o 'api' usa JWT.
    |
    | Cada guard aponta para um "provider" (model usado para autenticação).
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'jwt',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Define de onde os usuários são buscados.
    | No nosso caso, o provider 'users' usa o model App\Models\Usuario.
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\Usuario::class, // 👈 Aqui usamos o model real do sistema
        ],

        // Caso queira usar tabela direta sem Eloquent:
        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'tb_usuario',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Reset de Senha
    |--------------------------------------------------------------------------
    |
    | Configurações para redefinição de senha — não obrigatórias no JWT,
    | mas mantidas para compatibilidade caso o app use e-mail de recuperação.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,      // tempo de validade do token (minutos)
            'throttle' => 60,    // tempo de espera para nova solicitação
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tempo limite para confirmação de senha
    |--------------------------------------------------------------------------
    |
    | Quantos segundos até o usuário precisar reconfirmar a senha.
    | Por padrão: 3 horas (10800 segundos).
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];
