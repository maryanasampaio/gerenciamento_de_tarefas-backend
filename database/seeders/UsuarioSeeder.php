<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tb_usuario')->insert([
            'usuario' => 'admin',
            'senha' => Hash::make('12345678'),
            'nome_completo' => 'admin',
            'email' => 'admin@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
