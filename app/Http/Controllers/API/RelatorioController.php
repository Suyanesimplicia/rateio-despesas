<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Unidade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function porUnidade(Request $request)
    {
        $dados = $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        $totais = DB::table('rateios')
            ->join('despesas', 'despesas.id', '=', 'rateios.despesa_id')
            ->select(
                'rateios.unidade_id',
                DB::raw('SUM(rateios.valor_brl) AS total_brl')
            )
            ->whereBetween('despesas.data', [
                $dados['data_inicio'],
                $dados['data_fim'],
            ])
            ->groupBy('rateios.unidade_id');

        $relatorio = Unidade::query()
            ->leftJoinSub($totais, 'totais', function ($join) {
                $join->on('unidades.id', '=', 'totais.unidade_id');
            })
            ->select(
                'unidades.id',
                'unidades.nome',
                'unidades.empresa',
                DB::raw('COALESCE(totais.total_brl, 0) AS total_brl')
            )
            ->orderBy('unidades.nome')
            ->get();

        return response()->json([
            'data_inicio' => $dados['data_inicio'],
            'data_fim' => $dados['data_fim'],
            'unidades' => $relatorio,
        ]);
    }
}
