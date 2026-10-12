<?php

namespace App\Services;

use InvalidArgumentException;

class RateioService
{
    public function calcular(string $valorBrl, array $rateios): array
    {
        if (count($rateios) === 0) {
            throw new InvalidArgumentException('Informe pelo menos um rateio.');
        }

        // Trabalhamos em centavos para evitar erros com dinheiro.
        $totalCentavos = (int) round(((float) $valorBrl) * 100);

        $somaPercentuais = 0;
        $distribuicao = [];
        $centavosDistribuidos = 0;

        foreach ($rateios as $rateio) {
            $percentual = (int) round(
                ((float) $rateio['percentual']) * 10000
            );

            if ($percentual <= 0 || $percentual > 1000000) {
                throw new InvalidArgumentException(
                    'Cada percentual deve ser maior que zero e menor ou igual a 100.'
                );
            }

            $somaPercentuais += $percentual;
        }

        if ($somaPercentuais !== 1000000) {
            throw new InvalidArgumentException(
                'A soma dos percentuais deve ser exatamente 100%.'
            );
        }

        foreach ($rateios as $indice => $rateio) {
            $percentual = (int) round(
                ((float) $rateio['percentual']) * 10000
            );

            // A última unidade recebe o ajuste de centavos.
            if ($indice === count($rateios) - 1) {
                $centavos = $totalCentavos - $centavosDistribuidos;
            } else {
                $centavos = intdiv(
                    $totalCentavos * $percentual + 500000,
                    1000000
                );

                $centavosDistribuidos += $centavos;
            }

            $distribuicao[] = [
                'unidade_id' => $rateio['unidade_id'],
                'percentual' => $rateio['percentual'],
                'valor_brl' => number_format($centavos / 100, 2, '.', ''),
            ];
        }

        return $distribuicao;
    }
}