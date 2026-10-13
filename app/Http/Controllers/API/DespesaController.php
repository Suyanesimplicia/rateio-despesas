<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Despesa;
use App\Services\CotacaoService;
use App\Services\RateioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use RuntimeException;

class DespesaController extends Controller
{
    public function index()
    {
        return response()->json(
            Despesa::with('rateios')->get()
        );
    }

    public function store(
        Request $request,
        RateioService $rateioService,
        CotacaoService $cotacaoService
    ) {
        $dados = $request->validate([
            'descricao' => 'required|string|max:255',
            'fornecedor' => 'required|string|max:255',
            'data' => 'required|date|before_or_equal:today',
            'valor' => 'required|numeric|gt:0|decimal:0,2',
            'moeda' => 'required|in:BRL,USD',

            'rateios' => 'required|array|min:1',
            'rateios.*.unidade_id' => [
                'required',
                'integer',
                'distinct',
                'exists:unidades,id',
            ],
            'rateios.*.percentual' => [
                'required',
                'numeric',
                'gt:0',
                'lte:100',
                'decimal:0,4',
            ],
        ]);

        $cotacao = '1.00000000';
        $valorBrl = (string) $dados['valor'];

        if ($dados['moeda'] === 'USD') {
            try {
                $cotacao = $cotacaoService->obterCotacao(
                    $dados['data']
                );
            } catch (RuntimeException $erro) {
                return response()->json([
                    'message' => 'Não foi possível obter a cotação.',
                    'error' => $erro->getMessage(),
                ], 503);
            }

            // Converte para centavos, arredondando o valor final.
            $centavos = (int) round(
                (float) $dados['valor'] * (float) $cotacao * 100
            );

            $valorBrl = number_format(
                $centavos / 100,
                2,
                '.',
                ''
            );
        }

        try {
            $distribuicao = $rateioService->calcular(
                $valorBrl,
                $dados['rateios']
            );
        } catch (InvalidArgumentException $erro) {
            throw ValidationException::withMessages([
                'rateios' => [$erro->getMessage()],
            ]);
        }

        $despesa = DB::transaction(function () use (
            $dados,
            $cotacao,
            $valorBrl,
            $distribuicao
        ) {
            $despesa = Despesa::create([
                'descricao' => $dados['descricao'],
                'fornecedor' => $dados['fornecedor'],
                'data' => $dados['data'],
                'valor' => $dados['valor'],
                'moeda' => $dados['moeda'],
                'valor_brl' => $valorBrl,
                'cotacao' => $cotacao,
                'status_conversao' => 'convertido',
            ]);
            
            $despesa->rateios()->createMany($distribuicao);

            return $despesa;
        });

        return response()->json(
            $despesa->load('rateios'),
            201
        );
    }

    public function show(string $id)
    {
        return response()->json(
            Despesa::with('rateios')->findOrFail($id)
        );
    }
    public function update(
    Request $request,
    string $id,
    RateioService $rateioService,
    CotacaoService $cotacaoService
) {
    $despesa = Despesa::findOrFail($id);

    $dados = $request->validate([
        'descricao' => 'required|string|max:255',
        'fornecedor' => 'required|string|max:255',
        'data' => 'required|date|before_or_equal:today',
        'valor' => 'required|numeric|gt:0|decimal:0,2',
        'moeda' => 'required|in:BRL,USD',

        'rateios' => 'required|array|min:1',
        'rateios.*.unidade_id' => [
            'required',
            'integer',
            'distinct',
            'exists:unidades,id',
        ],
        'rateios.*.percentual' => [
            'required',
            'numeric',
            'gt:0',
            'lte:100',
            'decimal:0,4',
        ],
    ]);

    $cotacao = '1.00000000';
    $valorBrl = (string) $dados['valor'];

    if ($dados['moeda'] === 'USD') {
        try {
            $cotacao = $cotacaoService->obterCotacao(
                $dados['data']
            );
        } catch (RuntimeException $erro) {
            return response()->json([
                'message' => 'Não foi possível obter a cotação.',
                'error' => $erro->getMessage(),
            ], 503);
        }

        $centavos = (int) round(
            (float) $dados['valor'] * (float) $cotacao * 100
        );

        $valorBrl = number_format(
            $centavos / 100,
            2,
            '.',
            ''
        );
    }

    try {
        $distribuicao = $rateioService->calcular(
            $valorBrl,
            $dados['rateios']
        );
    } catch (InvalidArgumentException $erro) {
        throw ValidationException::withMessages([
            'rateios' => [$erro->getMessage()],
        ]);
    }

    DB::transaction(function () use (
        $despesa,
        $dados,
        $cotacao,
        $valorBrl,
        $distribuicao
    ) {
        $despesa->update([
            'descricao' => $dados['descricao'],
            'fornecedor' => $dados['fornecedor'],
            'data' => $dados['data'],
            'valor' => $dados['valor'],
            'moeda' => $dados['moeda'],
            'valor_brl' => $valorBrl,
            'cotacao' => $cotacao,
            'status_conversao' => 'convertido',
        ]);

        $despesa->rateios()->delete();
        $despesa->rateios()->createMany($distribuicao);
    });

    return response()->json(
        $despesa->fresh()->load('rateios')
    );
}

    public function destroy(string $id)
    {
        $despesa = Despesa::findOrFail($id);

        DB::transaction(function () use ($despesa) {
            $despesa->rateios()->delete();
            $despesa->delete();
        });

        return response()->json([
            'message' => 'Despesa excluída com sucesso.',
        ]);
    }
}