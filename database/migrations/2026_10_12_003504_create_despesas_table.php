<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('despesas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->string('fornecedor');
            $table->date('data');
            $table->decimal('valor', 15, 2);
            $table->enum('moeda', ['BRL', 'USD']);
            $table->decimal('valor_brl', 15, 2)->nullable();
            $table->decimal('cotacao', 18, 8)->nullable();
            $table->string('status_conversao')->default('pendente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despesas');
    }
};
