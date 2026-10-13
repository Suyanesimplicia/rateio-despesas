<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ImportacaoCsvService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ImportacaoController extends Controller
{
    public function importar(
        Request $request,
        ImportacaoCsvService $importacaoCsvService,
        DespesaController $despesaController
    ) {
        $request->validate([
            'arquivo' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        try {
            $linhas = $importacaoCsvService->ler(
                $request->file('arquivo')->getRealPath()
            );
        } catch (RuntimeException $erro) {
            return response()->json([
                'mensagem' => $erro->getMessage(),
            ], 422);
        }

        $importadas = 0;
        $erros = [];

        foreach ($linhas as $linha) {
            $numeroLinha = $linha['linha'];
            $campos = $linha['campos'];

            if (count($campos) !== 6) {
                $erros[] = [
                    'linha' => $numeroLinha,
                    'erro' => 'A linha deve conter exatamente 6 colunas.',
                ];
                continue;
            }

            [
                $data,
                $descricao,
                $fornecedor,
                $valor,
                $moeda,
                $rateioTexto
            ] = array_map('trim', $campos);

            $rateios = [];

            foreach (explode('|', $rateioTexto) as $parte) {
                $componentes = explode(':', $parte);

                if (count($componentes) !== 2) {
                    $rateios = [];
                    break;
                }

                $rateios[] = [
                    'unidade_id' => trim($componentes[0]),
                    'percentual' => trim($componentes[1]),
                ];
            }

            $dados = [
                'data' => $data,
                'descricao' => $descricao,
                'fornecedor' => $fornecedor,
                'valor' => $valor,
                'moeda' => $moeda,
                'rateios' => $rateios,
            ];

            try {
                $requisicao = Request::create(
                    '/api/despesas',
                    'POST',
                    $dados
                );

                $resposta = app()->call(
                    [$despesaController, 'store'],
                    ['request' => $requisicao]
                );

                if ($resposta->getStatusCode() !== 201) {
                    $conteudo = json_decode(
                        $resposta->getContent(),
                        true
                    );

                    $erros[] = [
                        'linha' => $numeroLinha,
                        'erro' => $conteudo['message']
                            ?? 'Não foi possível importar a despesa.',
                    ];

                    continue;
                }

                $importadas++;

            } catch (ValidationException $erro) {
                $erros[] = [
                    'linha' => $numeroLinha,
                    'erro' => $erro->errors(),
                ];

            } catch (Throwable $erro) {
                report($erro);

                $erros[] = [
                    'linha' => $numeroLinha,
                    'erro' => 'Erro interno ao processar a linha.',
                ];
            }
        }

        return response()->json([
            'mensagem' => 'Processamento do CSV concluído.',
            'total_linhas' => count($linhas),
            'importadas' => $importadas,
            'rejeitadas' => count($erros),
            'erros' => $erros,
        ]);
    }
}
