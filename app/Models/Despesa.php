<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Despesa extends Model
{
    protected $table = 'despesas';

    protected $fillable = [
        'descricao',
        'fornecedor',
        'data',
        'valor',
        'moeda',
        'valor_brl',
        'cotacao',
        'status_conversao',
    ];

    protected $casts = [
        'data' => 'date',
        'valor' => 'decimal:2',
        'valor_brl' => 'decimal:2',
        'cotacao' => 'decimal:8',
    ];

    public function rateios()
    {
        return $this->hasMany(Rateio::class);
    }
}
