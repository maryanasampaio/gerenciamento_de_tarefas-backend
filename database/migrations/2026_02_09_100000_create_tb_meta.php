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
        Schema::create('tb_meta', function (Blueprint $table) {
            $table->id('id_meta');
            $table->unsignedBigInteger('id_usuario');
            $table->string('titulo', 255);
            $table->text('descricao')->nullable();
            $table->string('contexto', 100)->nullable();
            $table->enum('prioridade', ['baixa', 'media', 'alta'])->default('baixa');
            $table->enum('status', ['pendente', 'em_andamento', 'concluida'])->default('pendente');
            $table->enum('tipo', ['diaria', 'mensal', 'anual']);
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_usuario')->references('id_usuario')->on('tb_usuario')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_meta');
    }
};
