<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tb_tarefa', function (Blueprint $table) {
            if (!Schema::hasColumn('tb_tarefa', 'descricao')) {
                $table->text('descricao')->nullable()->after('titulo');
            }
            if (!Schema::hasColumn('tb_tarefa', 'id_meta')) {
                $table->unsignedBigInteger('id_meta')->nullable()->after('id_usuario');
                $table->foreign('id_meta')->references('id_meta')->on('tb_meta')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_tarefa', function (Blueprint $table) {
            if (Schema::hasColumn('tb_tarefa', 'id_meta')) {
                $table->dropForeign(['id_meta']);
                $table->dropColumn('id_meta');
            }
            if (Schema::hasColumn('tb_tarefa', 'descricao')) {
                $table->dropColumn('descricao');
            }
        });
    }
};
