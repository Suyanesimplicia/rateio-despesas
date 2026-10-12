<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rateios', function (Blueprint $table) {
            $table->id();

            $table->foreignId('despesa_id')
                ->constrained('despesas')
                ->cascadeOnDelete();

            $table->foreignId('unidade_id')
                ->constrained('unidades')
                ->restrictOnDelete();

            $table->decimal('percentual', 7, 4);
            $table->decimal('valor_brl', 15, 2);

            $table->timestamps();

            $table->unique(['despesa_id', 'unidade_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rateios');
    }
};
