<?php

namespace Tests\Unit;

use App\Services\ImportacaoCsvService;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ImportacaoCsvServiceTest extends TestCase
{
    private function criarCsv(string $conteudo): string
    {
        $caminho = tempnam(sys_get_temp_dir(), 'rateio_csv_');

        if ($caminho === false) {
            throw new RuntimeException('Não foi possível criar o CSV de teste.');
        }

        file_put_contents($caminho, $conteudo);

        return $caminho;
    }

    public function test_le_csv_com_duas_despesas(): void
    {
        $csv = $this->criarCsv(
            "data;descricao;fornecedor;valor;moeda;rateio\n" .
            "2026-10-09;Internet;Claro;200.00;BRL;1:60|2:40\n" .
            "2026-10-10;Energia;Cemig;100.00;BRL;1:50|2:50\n"
        );

        try {
            $service = new ImportacaoCsvService();
            $resultado = $service->ler($csv);

            $this->assertCount(2, $resultado);

            $this->assertSame(2, $resultado[0]['linha']);
            $this->assertSame(3, $resultado[1]['linha']);

            $this->assertSame('Internet', $resultado[0]['campos'][1]);
            $this->assertSame('Energia', $resultado[1]['campos'][1]);
        } finally {
            unlink($csv);
        }
    }

    public function test_aceita_cabecalho_com_bom_utf8(): void
    {
        $csv = $this->criarCsv(
            "\xEF\xBB\xBFdata;descricao;fornecedor;valor;moeda;rateio\n" .
            "2026-10-09;Internet;Claro;200.00;BRL;1:100\n"
        );

        try {
            $service = new ImportacaoCsvService();
            $resultado = $service->ler($csv);

            $this->assertCount(1, $resultado);
            $this->assertSame('Internet', $resultado[0]['campos'][1]);
        } finally {
            unlink($csv);
        }
    }

    public function test_rejeita_cabecalho_invalido(): void
    {
        $csv = $this->criarCsv(
            "data;descricao;valor\n" .
            "2026-10-09;Internet;200.00\n"
        );

        try {
            $service = new ImportacaoCsvService();

            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Cabeçalho inválido');

            $service->ler($csv);
        } finally {
            unlink($csv);
        }
    }
}
