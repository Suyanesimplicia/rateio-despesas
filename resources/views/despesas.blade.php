<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Cadastro de Despesas - Sistema de Rateio</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f6fa;
            color: #253047;
        }

        header {
            background-color: #243b65;
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
            background-color: white;
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
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: bold;
        }

        input,
        select {
            width: 100%;
            padding: 11px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 15px;
            background-color: white;
        }

        input:focus,
        select:focus {
            outline: 2px solid #93c5fd;
        }

        button {
            background-color: #243b65;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 16px;
            font-size: 14px;
        }

        button:hover {
            background-color: #36558c;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .secundario {
            background-color: #64748b;
        }

        .rateio {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 10px;
            align-items: end;
            margin-top: 12px;
        }

        .rateio button {
            margin-top: 0;
        }

        .rateio input,
        .rateio select {
            height: 43px;
        }

        .total {
            font-weight: bold;
            margin-top: 18px;
        }

        .tabela {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        th {
            background-color: #f8fafc;
        }

        a {
            color: #243b65;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        #mensagem {
            margin-top: 15px;
            white-space: pre-line;
            font-weight: bold;
        }

        .sucesso {
            color: #15803d;
        }

        .erro {
            color: #b91c1c;
        }

        @media (max-width: 600px) {
            .rateio {
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
    <h1>Cadastro de Despesas</h1>
    <p>Registre despesas e distribua os valores entre unidades.</p>
</header>

<main>

    <p>
        <a href="/">← Voltar ao painel</a>
    </p>

    <section class="card">

        <h2>Nova despesa</h2>

        <form id="formDespesa">

            <label for="descricao">Descrição</label>
            <input
                type="text"
                id="descricao"
                maxlength="255"
                required
            >

            <label for="fornecedor">Fornecedor</label>
            <input
                type="text"
                id="fornecedor"
                maxlength="255"
                required
            >

            <label for="data">Data da despesa</label>
            <input
                type="date"
                id="data"
                required
            >

            <label for="valor">Valor</label>
            <input
                type="number"
                id="valor"
                min="0.01"
                step="0.01"
                required
            >

            <label for="moeda">Moeda</label>
            <select id="moeda" required>
                <option value="BRL">BRL - Real</option>
                <option value="USD">USD - Dólar</option>
            </select>

            <h3>Rateio entre unidades</h3>

            <p>
                Selecione as unidades e informe os percentuais.
                A soma deve ser exatamente 100%.
            </p>

            <div id="rateios"></div>

            <button
                type="button"
                id="adicionarRateio"
                class="secundario"
            >
                + Adicionar unidade ao rateio
            </button>

            <p id="totalPercentual" class="total">
                Total do rateio: 0%
            </p>

            <button type="submit" id="salvar">
                Cadastrar despesa
            </button>

        </form>

        <p id="mensagem" role="status"></p>

    </section>

    <section class="card">

        <h2>Despesas cadastradas</h2>

        <div class="tabela">

            <table>

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descrição</th>
                        <th>Fornecedor</th>
                        <th>Data</th>
                        <th>Valor original</th>
                        <th>Valor em BRL</th>
                    </tr>
                </thead>

                <tbody id="listaDespesas">
                    <tr>
                        <td colspan="6">
                            Carregando despesas...
                        </td>
                    </tr>
                </tbody>

            </table>

        </div>

    </section>

</main>

<script>

    const formulario = document.getElementById('formDespesa');
    const areaRateios = document.getElementById('rateios');
    const mensagem = document.getElementById('mensagem');
    const botaoSalvar = document.getElementById('salvar');
    const listaDespesas = document.getElementById('listaDespesas');

    let unidades = [];

    function mostrarMensagem(texto, tipo) {
        mensagem.textContent = texto;
        mensagem.className = tipo || '';
    }

    function atualizarTotal() {

        const campos = document.querySelectorAll('.percentual');

        let total = 0;

        campos.forEach(function(campo) {
            total += Number(campo.value || 0);
        });

        document.getElementById('totalPercentual').textContent =
            'Total do rateio: ' + total.toFixed(4) + '%';
    }

    function adicionarRateio() {

        if (unidades.length === 0) {
            mostrarMensagem(
                'Nenhuma unidade disponível. Cadastre uma unidade primeiro.',
                'erro'
            );
            return;
        }

        const linha = document.createElement('div');
        linha.className = 'rateio';

        const seletor = document.createElement('select');
        seletor.className = 'unidade';
        seletor.required = true;

        unidades.forEach(function(unidade) {

            const opcao = document.createElement('option');

            opcao.value = unidade.id;
            opcao.textContent =
                unidade.nome + ' - ' + unidade.empresa;

            seletor.appendChild(opcao);
        });

        const percentual = document.createElement('input');

        percentual.className = 'percentual';
        percentual.type = 'number';
        percentual.min = '0.0001';
        percentual.max = '100';
        percentual.step = '0.0001';
        percentual.placeholder = 'Percentual (%)';
        percentual.required = true;

        percentual.addEventListener('input', atualizarTotal);

        const remover = document.createElement('button');

        remover.type = 'button';
        remover.className = 'secundario';
        remover.textContent = 'Remover';

        remover.addEventListener('click', function() {
            linha.remove();
            atualizarTotal();
        });

        linha.appendChild(seletor);
        linha.appendChild(percentual);
        linha.appendChild(remover);

        areaRateios.appendChild(linha);

        atualizarTotal();
    }

    async function carregarUnidades() {

        try {

            const resposta = await fetch('/api/unidades', {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!resposta.ok) {
                throw new Error(
                    'Erro ao consultar as unidades. Código HTTP: ' +
                    resposta.status
                );
            }

            const resultado = await resposta.json();

            unidades = Array.isArray(resultado)
                ? resultado
                : resultado.data;

            if (!Array.isArray(unidades)) {
                throw new Error(
                    'A API retornou um formato inesperado para as unidades.'
                );
            }

            areaRateios.replaceChildren();

            if (unidades.length > 0) {
                adicionarRateio();
            } else {
                mostrarMensagem(
                    'Cadastre uma unidade antes de registrar despesas.',
                    'erro'
                );
            }

        } catch (erro) {

            mostrarMensagem(
                'Não foi possível carregar as unidades: ' + erro.message,
                'erro'
            );
        }
    }

    function adicionarCelula(linha, valor) {

        const celula = linha.insertCell();

        celula.textContent = valor == null ? '' : String(valor);
    }

    async function carregarDespesas() {

        try {

            const resposta = await fetch('/api/despesas', {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!resposta.ok) {
                throw new Error(
                    'Erro ao consultar despesas. Código HTTP: ' +
                    resposta.status
                );
            }

            const resultado = await resposta.json();

            const despesas = Array.isArray(resultado)
                ? resultado
                : resultado.data;

            if (!Array.isArray(despesas)) {
                throw new Error(
                    'A API retornou um formato inesperado para as despesas.'
                );
            }

            listaDespesas.replaceChildren();

            if (despesas.length === 0) {

                const linha = listaDespesas.insertRow();
                const celula = linha.insertCell();

                celula.colSpan = 6;
                celula.textContent = 'Nenhuma despesa cadastrada.';

                return;
            }

            despesas.forEach(function(despesa) {

                const linha = listaDespesas.insertRow();

                adicionarCelula(linha, despesa.id);
                adicionarCelula(linha, despesa.descricao);
                adicionarCelula(linha, despesa.fornecedor);

                const data = despesa.data
                    ? String(despesa.data).slice(0, 10)
                    : '';

                adicionarCelula(linha, data);

                adicionarCelula(
                    linha,
                    despesa.moeda + ' ' + despesa.valor
                );

                adicionarCelula(
                    linha,
                    'R$ ' + despesa.valor_brl
                );
            });

        } catch (erro) {

            listaDespesas.replaceChildren();

            const linha = listaDespesas.insertRow();
            const celula = linha.insertCell();

            celula.colSpan = 6;
            celula.textContent =
                'Não foi possível carregar as despesas: ' + erro.message;
        }
    }

    document.getElementById('adicionarRateio')
        .addEventListener('click', adicionarRateio);

    formulario.addEventListener('submit', async function(evento) {

        evento.preventDefault();

        const linhas = document.querySelectorAll('.rateio');

        const rateios = Array.from(linhas).map(function(linha) {

            return {
                unidade_id: Number(
                    linha.querySelector('.unidade').value
                ),
                percentual: Number(
                    linha.querySelector('.percentual').value
                )
            };
        });

        if (rateios.length === 0) {

            mostrarMensagem(
                'Adicione pelo menos uma unidade ao rateio.',
                'erro'
            );

            return;
        }

        const total = rateios.reduce(function(soma, item) {
            return soma + item.percentual;
        }, 0);

        if (Math.abs(total - 100) > 0.000001) {

            mostrarMensagem(
                'Os percentuais devem somar exatamente 100%.',
                'erro'
            );

            return;
        }

        const ids = rateios.map(function(item) {
            return item.unidade_id;
        });

        if (new Set(ids).size !== ids.length) {

            mostrarMensagem(
                'A mesma unidade não pode aparecer duas vezes no rateio.',
                'erro'
            );

            return;
        }

        const dados = {

            descricao: document.getElementById('descricao').value.trim(),

            fornecedor: document.getElementById('fornecedor').value.trim(),

            data: document.getElementById('data').value,

            valor: Number(document.getElementById('valor').value),

            moeda: document.getElementById('moeda').value,

            rateios: rateios
        };

        botaoSalvar.disabled = true;

        mostrarMensagem('Salvando despesa...', '');

        try {

            const resposta = await fetch('/api/despesas', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },

                body: JSON.stringify(dados)
            });

            const resultado = await resposta.json();

            if (!resposta.ok) {

                let detalhes = resultado.message ||
                    'Não foi possível cadastrar a despesa.';

                if (resultado.errors) {

                    detalhes = Object.values(resultado.errors)
                        .flat()
                        .join(' ');
                }

                throw new Error(detalhes);
            }

            mostrarMensagem(
                'Despesa cadastrada com sucesso!',
                'sucesso'
            );

            formulario.reset();

            areaRateios.replaceChildren();

            adicionarRateio();

            await carregarDespesas();

        } catch (erro) {

            mostrarMensagem(
                'Erro ao cadastrar despesa: ' + erro.message,
                'erro'
            );

        } finally {

            botaoSalvar.disabled = false;
        }
    });

    carregarUnidades();

    carregarDespesas();

</script>

</body>
</html>