<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CotacaoService
{
    public function obterCotacao(string $data): string
    {
        $dataDespesa = Carbon::parse($data)->startOfDay();

        if ($dataDespesa->isFuture()) {
            throw new RuntimeException(
                'Não é possível consultar cotação de uma data futura.'
            );
        }

        // Procura a cotação na data ou nos dias anteriores.
        // Isso contempla fins de semana e feriados.
        for ($dias = 0; $dias <= 7; $dias++) {
            $dataConsulta = $dataDespesa
                ->copy()
                ->subDays($dias)
                ->format('m-d-Y');

            try {
                $resposta = Http::timeout(10)
                    ->retry(2, 500)
                    ->get(
                        'https://olinda.bcb.gov.br/olinda/servico/PTAX/versao/v1/odata/CotacaoDolarDia(dataCotacao=@dataCotacao)',
                        [
                            '@dataCotacao' => "'{$dataConsulta}'",
                            '$top' => 1,
                            '$format' => 'json',
                        ]
                    );

                if (!$resposta->successful()) {
                    throw new RuntimeException(
                        'O Banco Central não respondeu corretamente.'
                    );
                }

                $cotacoes = $resposta->json('value', []);

                if (!empty($cotacoes)) {
                    return (string) $cotacoes[0]['cotacaoVenda'];
                }
            } catch (\Illuminate\Http\Client\ConnectionException $erro) {
                throw new RuntimeException(
                    'Serviço de cotação indisponível. Tente novamente mais tarde.',
                    0,
                    $erro
                );
            }
        }

        throw new RuntimeException(
            'Não foi encontrada cotação para a data informada.'
        );
    }
}