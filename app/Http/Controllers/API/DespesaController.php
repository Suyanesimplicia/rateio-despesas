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
        $dados = $this->validarDados($request);

        $conversao = $this->prepararConversao(
            $dados,
            $cotacaoService
        );

        $distribuicao = $this->prepararRateios(
            $dados,
            $conversao['valor_brl'],
            $rateioService
        );

        $despesa = DB::transaction(function () use (
            $dados,
            $conversao,
            $distribuicao
        ) {
            $despesa = Despesa::create([
                'descricao' => $dados['descricao'],
                'fornecedor' => $dados['fornecedor'],
                'data' => $dados['data'],
                'valor' => $dados['valor'],
                'moeda' => $dados['moeda'],
                'valor_brl' => $conversao['valor_brl'],
                'cotacao' => $conversao['cotacao'],
                'status_conversao' => $conversao['status'],
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

        $dados = $this->validarDados($request);

        $conversao = $this->prepararConversao(
            $dados,
            $cotacaoService
        );

        $distribuicao = $this->prepararRateios(
            $dados,
            $conversao['valor_brl'],
            $rateioService
        );

        DB::transaction(function () use (
            $despesa,
            $dados,
            $conversao,
            $distribuicao
        ) {
            $despesa->update([
                'descricao' => $dados['descricao'],
                'fornecedor' => $dados['fornecedor'],
                'data' => $dados['data'],
                'valor' => $dados['valor'],
                'moeda' => $dados['moeda'],
                'valor_brl' => $conversao['valor_brl'],
                'cotacao' => $conversao['cotacao'],
                'status_conversao' => $conversao['status'],
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

    private function validarDados(Request $request): array
    {
        return $request->validate([
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
    }

    private function prepararConversao(
        array $dados,
        CotacaoService $cotacaoService
    ): array {
        if ($dados['moeda'] === 'BRL') {
            return [
                'cotacao' => '1.00000000',
                'valor_brl' => (string) $dados['valor'],
                'status' => 'convertido',
            ];
        }

        try {
            $cotacao = $cotacaoService->obterCotacao(
                $dados['data']
            );
        } catch (RuntimeException $erro) {
            // A despesa é preservada para conversão posterior.
            return [
                'cotacao' => null,
                'valor_brl' => null,
                'status' => 'pendente',
            ];
        }

        // Converte o valor total e arredonda para centavos.
        $centavos = (int) round(
            (float) $dados['valor'] * (float) $cotacao * 100
        );

        $valorBrl = number_format(
            $centavos / 100,
            2,
            '.',
            ''
        );

        return [
            'cotacao' => $cotacao,
            'valor_brl' => $valorBrl,
            'status' => 'convertido',
        ];
    }

    private function prepararRateios(
        array $dados,
        ?string $valorBrl,
        RateioService $rateioService
    ): array {
        try {
            // Mesmo pendente, valida se os percentuais somam 100%.
            $distribuicao = $rateioService->calcular(
                $valorBrl ?? '0.00',
                $dados['rateios']
            );
        } catch (InvalidArgumentException $erro) {
            throw ValidationException::withMessages([
                'rateios' => [$erro->getMessage()],
            ]);
        }

        if ($valorBrl === null) {
            // Mantém os percentuais, sem inventar valores em BRL.
            foreach ($distribuicao as &$rateio) {
                $rateio['valor_brl'] = null;
            }

            unset($rateio);
        }

        return $distribuicao;
    }
}