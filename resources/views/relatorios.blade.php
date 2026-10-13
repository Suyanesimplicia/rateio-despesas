<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Sistema de Rateio</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fa;
            color: #253047;
        }

        header {
            background: #243b65;
            color: white;
            padding: 28px 40px;
        }

        header h1 {
            margin: 0 0 8px;
        }

        header p {
            margin: 0;
        }

        main {
            max-width: 1000px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        h2 {
            margin-top: 0;
        }

        .filtros {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
        }

        button {
            background: #243b65;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 20px;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        a {
            color: #243b65;
            text-decoration: none;
        }

        .tabela {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        .total {
            margin-top: 25px;
            padding: 20px;
            background: #f1f5f9;
            border-radius: 8px;
        }

        .total strong {
            display: block;
            font-size: 26px;
            margin-top: 8px;
        }

        .erro {
            color: #b91c1c;
        }

        #mensagem {
            white-space: pre-line;
        }

        @media (max-width: 600px) {
            .filtros {
                grid-template-columns: 1fr;
            }

            header {
                padding: 24px 20px;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Relatórios de Despesas</h1>
    <p>Consulte os valores rateados por unidade e período.</p>
</header>

<main>

    <p>
        <a href="/">← Voltar ao painel</a>
    </p>

    <section class="card">

        <h2>Filtrar por período</h2>

        <form id="formRelatorio">

            <div class="filtros">

                <div>
                    <label for="dataInicio">Data inicial</label>
                    <input
                        type="date"
                        id="dataInicio"
                        value="2026-10-01"
                        required
                    >
                </div>

                <div>
                    <label for="dataFim">Data final</label>
                    <input
                        type="date"
                        id="dataFim"
                        value="2026-10-31"
                        required
                    >
                </div>

            </div>

            <button type="submit" id="botaoConsultar">
                Consultar relatório
            </button>

        </form>

        <p id="mensagem" role="status"></p>

    </section>

    <section class="card" id="resultado" hidden>

        <h2>Despesas por unidade</h2>

        <div class="tabela">

            <table>
                <thead>
                    <tr>
                        <th>Unidade</th>
                        <th>Empresa</th>
                        <th>Total em BRL</th>
                    </tr>
                </thead>

                <tbody id="listaRelatorio"></tbody>
            </table>

        </div>

        <div class="total">
            Total geral do período
            <strong id="totalGeral">R$ 0,00</strong>
        </div>

    </section>

</main>

<script>
    const formulario = document.getElementById('formRelatorio');
    const mensagem = document.getElementById('mensagem');
    const botaoConsultar = document.getElementById('botaoConsultar');
    const listaRelatorio = document.getElementById('listaRelatorio');

    function formatarMoeda(valor) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(Number(valor) || 0);
    }

    function adicionarCelula(linha, texto) {
        const celula = linha.insertCell();
        celula.textContent = texto == null ? '' : String(texto);
    }

    formulario.addEventListener('submit', async function(evento) {
        evento.preventDefault();

        const inicio = document.getElementById('dataInicio').value;
        const fim = document.getElementById('dataFim').value;

        if (inicio > fim) {
            mensagem.textContent =
                'A data inicial não pode ser posterior à data final.';
            mensagem.className = 'erro';
            return;
        }

        botaoConsultar.disabled = true;
        mensagem.textContent = 'Consultando relatório...';
        mensagem.className = '';

        document.getElementById('resultado').hidden = true;

        try {
            const parametros = new URLSearchParams({
                data_inicio: inicio,
                data_fim: fim
            });

            const resposta = await fetch(
                '/api/relatorios/unidades?' + parametros.toString(),
                {
                    headers: {
                        'Accept': 'application/json'
                    }
                }
            );

            const dados = await resposta.json();

            if (!resposta.ok) {
                throw new Error(
                    dados.message || 'Não foi possível consultar o relatório.'
                );
            }

            const unidades = Array.isArray(dados)
                ? dados
                : (Array.isArray(dados.unidades)
                    ? dados.unidades
                    : dados.data);

            if (!Array.isArray(unidades)) {
                throw new Error(
                    'A API retornou um formato inesperado para o relatório.'
                );
            }

            listaRelatorio.replaceChildren();

            let totalGeral = 0;

            if (unidades.length === 0) {
                const linha = listaRelatorio.insertRow();
                const celula = linha.insertCell();

                celula.colSpan = 3;
                celula.textContent =
                    'Nenhuma unidade encontrada para este período.';
            }

            unidades.forEach(function(unidade) {
                const linha = listaRelatorio.insertRow();

                const nome = unidade.nome ?? unidade.unidade ?? '';
                const empresa = unidade.empresa ?? '';

                const valor = Number(
                    unidade.total_brl ??
                    unidade.total ??
                    unidade.valor_brl ??
                    0
                );

                totalGeral += valor;

                adicionarCelula(linha, nome);
                adicionarCelula(linha, empresa);
                adicionarCelula(linha, formatarMoeda(valor));
            });

            document.getElementById('totalGeral').textContent =
                formatarMoeda(totalGeral);

            document.getElementById('resultado').hidden = false;

            mensagem.textContent = 'Relatório consultado com sucesso!';

        } catch (erro) {
            mensagem.textContent =
                'Erro ao consultar relatório: ' + erro.message;
            mensagem.className = 'erro';

        } finally {
            botaoConsultar.disabled = false;
        }
    });
</script>

</body>
</html>