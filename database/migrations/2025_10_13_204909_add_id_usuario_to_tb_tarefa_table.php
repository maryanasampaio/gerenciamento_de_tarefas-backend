<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tb_tarefa', function (Blueprint $table) {
            // adiciona a coluna apenas se não existir
            if (!Schema::hasColumn('tb_tarefa', 'id_usuario')) {
                $table->unsignedBigInteger('id_usuario')->nullable()->after('id_tarefa');

                // cria a foreign key com tb_usuario
                $table->foreign('id_usuario')
                    ->references('id_usuario')
                    ->on('tb_usuario')
                    ->onDelete('cascade'); // apaga tarefas se o usuário for removido
            }
        });
    }

    public function down(): void
    {
        Schema::table('tb_tarefa', function (Blueprint $table) {
            if (Schema::hasColumn('tb_tarefa', 'id_usuario')) {
                $table->dropForeign(['id_usuario']);
                $table->dropColumn('id_usuario');
            }
        });
    }
};
