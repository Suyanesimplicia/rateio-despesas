<?php

namespace App\Services;

use RuntimeException;
use SplFileObject;

class ImportacaoCsvService
{
    public function ler(string $caminho): array
    {
        $arquivo = new SplFileObject($caminho, 'r');

        $arquivo->setFlags(
            SplFileObject::READ_CSV |
            SplFileObject::SKIP_EMPTY |
            SplFileObject::DROP_NEW_LINE
        );

        $arquivo->setCsvControl(';');

        $cabecalhoEsperado = [
            'data',
            'descricao',
            'fornecedor',
            'valor',
            'moeda',
            'rateio',
        ];

        $cabecalho = $arquivo->fgetcsv();

        if (!is_array($cabecalho)) {
            throw new RuntimeException('O arquivo CSV está vazio.');
        }

        $cabecalho = array_map(
            fn ($campo) => trim((string) $campo),
            $cabecalho
        );

        // Remove o BOM UTF-8, quando presente.
        $cabecalho[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cabecalho[0]);

        if ($cabecalho !== $cabecalhoEsperado) {
            throw new RuntimeException(
                'Cabeçalho inválido. Esperado: data;descricao;fornecedor;valor;moeda;rateio'
            );
        }

        $linhas = [];

        while (!$arquivo->eof()) {
            $campos = $arquivo->fgetcsv();
            $numeroLinha = $arquivo->key() + 1;

            if ($campos === false || $campos === [null]) {
                continue;
            }

            $linhas[] = [
                'linha' => $numeroLinha,
                'campos' => $campos,
            ];
        }

        return $linhas;
    }
}