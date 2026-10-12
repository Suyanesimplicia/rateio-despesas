<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rateio extends Model
{
    protected $table = 'rateios';

    protected $fillable = [
        'despesa_id',
        'unidade_id',
        'percentual',
        'valor_brl',
    ];

    protected $casts = [
        'percentual' => 'decimal:4',
        'valor_brl' => 'decimal:2',
    ];

    public function despesa()
    {
        return $this->belongsTo(Despesa::class);
    }

    public function unidade()
    {
        return $this->belongsTo(Unidade::class);
    }
}
