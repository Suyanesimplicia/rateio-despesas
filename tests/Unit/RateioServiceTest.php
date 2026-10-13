<?php

namespace Tests\Unit;

use App\Services\RateioService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RateioServiceTest extends TestCase
{
    public function test_divide_valor_entre_duas_unidades(): void
    {
        $service = new RateioService();

        $resultado = $service->calcular('100.00', [
            ['unidade_id' => 1, 'percentual' => 60],
            ['unidade_id' => 2, 'percentual' => 40],
        ]);

        $this->assertCount(2, $resultado);
        $this->assertSame('60.00', $resultado[0]['valor_brl']);
        $this->assertSame('40.00', $resultado[1]['valor_brl']);
    }

    public function test_distribui_centavos_sem_perder_valor(): void
    {
        $service = new RateioService();

        $resultado = $service->calcular('100.00', [
            ['unidade_id' => 1, 'percentual' => 33.33],
            ['unidade_id' => 2, 'percentual' => 33.33],
            ['unidade_id' => 3, 'percentual' => 33.34],
        ]);

        $totalCentavos = 0;

        foreach ($resultado as $rateio) {
            $totalCentavos += (int) round(
                ((float) $rateio['valor_brl']) * 100
            );
        }

        $this->assertSame(10000, $totalCentavos);
        $this->assertSame('33.33', $resultado[0]['valor_brl']);
        $this->assertSame('33.33', $resultado[1]['valor_brl']);
        $this->assertSame('33.34', $resultado[2]['valor_brl']);
    }

    public function test_rejeita_percentuais_que_nao_somam_cem(): void
    {
        $service = new RateioService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'A soma dos percentuais deve ser exatamente 100%.'
        );

        $service->calcular('100.00', [
            ['unidade_id' => 1, 'percentual' => 60],
            ['unidade_id' => 2, 'percentual' => 30],
        ]);
    }

    public function test_rejeita_rateio_vazio(): void
    {
        $service = new RateioService();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Informe pelo menos um rateio.'
        );

        $service->calcular('100.00', []);
    }
}
