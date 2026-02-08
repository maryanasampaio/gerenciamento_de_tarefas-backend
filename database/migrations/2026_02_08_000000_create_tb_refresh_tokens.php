<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tb_refresh_tokens', function (Blueprint $table) {
            $table->id('id_refresh_token');
            $table->unsignedBigInteger('id_usuario');
            $table->string('token', 500)->unique();
            $table->timestamp('expires_at');
            $table->boolean('revoked')->default(false);
            $table->timestamps();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('tb_usuario')
                ->onDelete('cascade');

            $table->index(['id_usuario', 'revoked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tb_refresh_tokens');
    }
};
