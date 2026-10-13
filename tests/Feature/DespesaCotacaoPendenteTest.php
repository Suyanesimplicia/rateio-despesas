<?php

namespace Tests\Feature;

use App\Models\Despesa;
use App\Models\Unidade;
use App\Services\CotacaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class DespesaCotacaoPendenteTest extends TestCase
{
    use RefreshDatabase;

    public function test_salva_despesa_em_usd_quando_cotacao_falha(): void
    {
        // Simula a indisponibilidade da API de cotação.
        // Nenhuma chamada real ao Banco Central será realizada.
        $this->mock(CotacaoService::class, function ($mock) {
            $mock->shouldReceive('obterCotacao')
                ->once()
                ->andThrow(
                    new RuntimeException('Serviço de cotação indisponível.')
                );
        });

        $unidadeA = Unidade::create([
            'nome' => 'Unidade A',
            'empresa' => 'Empresa Teste',
        ]);

        $unidadeB = Unidade::create([
            'nome' => 'Unidade B',
            'empresa' => 'Empresa Teste',
        ]);

        $resposta = $this->postJson('/api/despesas', [
            'descricao' => 'Licença de software',
            'fornecedor' => 'Fornecedor Internacional',
            'data' => '2026-09-01',
            'valor' => 150,
            'moeda' => 'USD',
            'rateios' => [
                [
                    'unidade_id' => $unidadeA->id,
                    'percentual' => 60,
                ],
                [
                    'unidade_id' => $unidadeB->id,
                    'percentual' => 40,
                ],
            ],
        ]);

        $resposta->assertCreated()
            ->assertJsonPath('moeda', 'USD')
            ->assertJsonPath('status_conversao', 'pendente')
            ->assertJsonPath('valor_brl', null)
            ->assertJsonPath('cotacao', null)
            ->assertJsonCount(2, 'rateios');

        $despesa = Despesa::with('rateios')->firstOrFail();

        $this->assertEquals('150.00', $despesa->valor);
        $this->assertEquals('pendente', $despesa->status_conversao);
        $this->assertNull($despesa->valor_brl);
        $this->assertNull($despesa->cotacao);
        $this->assertCount(2, $despesa->rateios);

        $percentuais = $despesa->rateios
            ->pluck('percentual')
            ->map(fn ($percentual) => (float) $percentual)
            ->sort()
            ->values()
            ->all();

        $this->assertEquals([40.0, 60.0], $percentuais);

        foreach ($despesa->rateios as $rateio) {
            $this->assertNull($rateio->valor_brl);
        }
    }
}
