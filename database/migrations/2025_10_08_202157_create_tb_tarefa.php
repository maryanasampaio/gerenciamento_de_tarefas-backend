<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_tarefa', function (Blueprint $table) {
            $table->id('id_tarefa');
            $table->string('titulo', 255);
            $table->enum('importancia', ['baixa', 'media', 'alta'])->default('baixa');
            $table->enum('status', ['pendente', 'em_andamento', 'concluida',])->default('pendente');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_tarefa');
    }
};
