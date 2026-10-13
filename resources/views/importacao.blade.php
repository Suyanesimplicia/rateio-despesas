<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Importação CSV - Sistema de Rateio</title>

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
            max-width: 950px;
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

        label {
            display: block;
            margin: 16px 0 8px;
            font-weight: bold;
        }

        input[type="file"] {
            display: block;
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: white;
        }

        button {
            background: #243b65;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 18px;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        a {
            color: #243b65;
            text-decoration: none;
        }

        .resumo {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-top: 20px;
        }

        .indicador {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 18px;
            text-align: center;
        }

        .indicador strong {
            display: block;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .indicador span {
            font-size: 14px;
        }

        .sucesso {
            color: #15803d;
        }

        .erro {
            color: #b91c1c;
        }

        .tabela {
            overflow-x: auto;
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f8fafc;
        }

        #mensagem {
            white-space: pre-line;
            font-weight: bold;
        }

        code {
            background: #eef2f7;
            padding: 3px 5px;
            border-radius: 4px;
            overflow-wrap: anywhere;
        }

        @media (max-width: 600px) {
            header {
                padding: 24px 20px;
            }

            .resumo {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

<header>
    <h1>Importação de Despesas CSV</h1>
    <p>Importe várias despesas de uma só vez.</p>
</header>

<main>

    <p>
        <a href="/">← Voltar ao painel</a>
    </p>

    <section class="card">

        <h2>Selecionar arquivo</h2>

        <p>
            O arquivo deve utilizar ponto e vírgula
            como separador e conter as colunas:
        </p>

        <p>
            <code>data;descricao;fornecedor;valor;moeda;rateio</code>
        </p>

        <p>
            Exemplo de rateio:
            <code>1:60|2:40</code>
        </p>

        <p>
            Nesse exemplo, os números 1 e 2 representam
            os IDs das unidades cadastradas.
        </p>

        <form id="formImportacao">

            <label for="arquivo">Arquivo CSV</label>

            <input
                type="file"
                id="arquivo"
                name="arquivo"
                accept=".csv,text/csv"
                required
            >

            <button type="submit" id="botaoImportar">
                Importar despesas
            </button>

        </form>

        <p id="mensagem" role="status"></p>

    </section>

    <section class="card" id="resultado" hidden>

        <h2>Resultado da importação</h2>

        <div class="resumo">

            <div class="indicador">
                <strong id="totalLinhas">0</strong>
                <span>Total de linhas</span>
            </div>

            <div class="indicador">
                <strong id="importadas" class="sucesso">0</strong>
                <span>Importadas</span>
            </div>

            <div class="indicador">
                <strong id="rejeitadas" class="erro">0</strong>
                <span>Rejeitadas</span>
            </div>

        </div>

        <div id="areaErros" hidden>

            <h3>Erros encontrados</h3>

            <div class="tabela">
                <table>
                    <thead>
                        <tr>
                            <th>Linha</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>

                    <tbody id="listaErros"></tbody>
                </table>
            </div>

        </div>

    </section>

</main>

<script>
    const formulario = document.getElementById('formImportacao');
    const campoArquivo = document.getElementById('arquivo');
    const botaoImportar = document.getElementById('botaoImportar');
    const mensagem = document.getElementById('mensagem');

    function mostrarMensagem(texto, tipo) {
        mensagem.textContent = texto;
        mensagem.className = tipo || '';
    }

    function formatarErro(erro) {
        if (typeof erro === 'string') {
            return erro;
        }

        if (Array.isArray(erro)) {
            return erro.map(formatarErro).join('; ');
        }

        if (erro && typeof erro === 'object') {
            return Object.values(erro).map(formatarErro).join('; ');
        }

        return String(erro == null ? 'Erro não informado' : erro);
    }

    formulario.addEventListener('submit', async function(evento) {
        evento.preventDefault();

        if (campoArquivo.files.length === 0) {
            mostrarMensagem('Selecione um arquivo CSV.', 'erro');
            return;
        }

        const arquivo = campoArquivo.files[0];

        if (!arquivo.name.toLowerCase().endsWith('.csv')) {
            mostrarMensagem('Selecione um arquivo com extensão .csv.', 'erro');
            return;
        }

        const dados = new FormData();
        dados.append('arquivo', arquivo);

        botaoImportar.disabled = true;
        document.getElementById('resultado').hidden = true;

        mostrarMensagem('Importando arquivo. Aguarde...', '');

        try {
            const resposta = await fetch('/api/despesas/importar', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: dados
            });

            const resultado = await resposta.json();

            if (!resposta.ok) {
                const detalhes = resultado.errors
                    ? formatarErro(resultado.errors)
                    : resultado.message || 'Falha na importação.';

                throw new Error(detalhes);
            }

            document.getElementById('totalLinhas').textContent =
                resultado.total_linhas ?? 0;

            document.getElementById('importadas').textContent =
                resultado.importadas ?? 0;

            document.getElementById('rejeitadas').textContent =
                resultado.rejeitadas ?? 0;

            const listaErros = document.getElementById('listaErros');
            listaErros.replaceChildren();

            const erros = Array.isArray(resultado.erros)
                ? resultado.erros
                : [];

            erros.forEach(function(erro) {
                const linha = listaErros.insertRow();

                const colunaLinha = linha.insertCell();
                const colunaMotivo = linha.insertCell();

                colunaLinha.textContent = erro.linha ?? '-';

                const motivo = erro.erro ?? erro.mensagem
                    ?? erro.errors ?? erro;

                colunaMotivo.textContent = formatarErro(motivo);
            });

            document.getElementById('areaErros').hidden =
                erros.length === 0;

            document.getElementById('resultado').hidden = false;

            mostrarMensagem(
                'Importação processada. Confira o resultado abaixo.',
                'sucesso'
            );

        } catch (erro) {
            mostrarMensagem(
                'Não foi possível importar o arquivo: ' + erro.message,
                'erro'
            );

        } finally {
            botaoImportar.disabled = false;
        }
    });
</script>

</body>
</html>