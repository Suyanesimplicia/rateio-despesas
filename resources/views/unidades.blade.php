<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unidades | Rateio de Despesas</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6fa;
            color: #253047;
        }

        header {
            background: #243b65;
            color: white;
            padding: 24px 40px;
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
            box-shadow: 0 3px 12px rgba(0,0,0,.08);
        }

        input {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            margin: 8px 0 15px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        button {
            background: #243b65;
            color: white;
            border: 0;
            padding: 12px 20px;
            border-radius: 6px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        #mensagem {
            margin-top: 15px;
        }

        a {
            color: #243b65;
        }
    </style>
</head>
<body>
    <header>
        <h1>Cadastro de Unidades</h1>
        <p>Gerencie as unidades das empresas</p>
    </header>

    <main>
        <p><a href="/">← Voltar ao painel</a></p>

        <div class="card">
            <h2>Nova unidade</h2>

            <form id="formUnidade">
                <label for="nome">Nome da unidade</label>
                <input id="nome" name="nome" required maxlength="255">

                <label for="empresa">Empresa</label>
                <input id="empresa" name="empresa" required maxlength="255">

                <button type="submit">Cadastrar unidade</button>
            </form>

            <p id="mensagem" role="status"></p>
        </div>

        <div class="card">
            <h2>Unidades cadastradas</h2>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Empresa</th>
                    </tr>
                </thead>
                <tbody id="listaUnidades">
                    <tr>
                        <td colspan="3">Carregando unidades...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        const formulario = document.getElementById('formUnidade');
        const mensagem = document.getElementById('mensagem');
        const lista = document.getElementById('listaUnidades');

        async function carregarUnidades() {
            try {
                const resposta = await fetch('/api/unidades', {
                    headers: { 'Accept': 'application/json' }
                });

                if (!resposta.ok) {
                    throw new Error('Erro ao consultar unidades.');
                }

                const unidades = await resposta.json();
                lista.replaceChildren();

                if (unidades.length === 0) {
                    const linha = lista.insertRow();
                    const celula = linha.insertCell();
                    celula.colSpan = 3;
                    celula.textContent = 'Nenhuma unidade cadastrada.';
                    return;
                }

                unidades.forEach(unidade => {
                    const linha = lista.insertRow();

                    [unidade.id, unidade.nome, unidade.empresa].forEach(valor => {
                        const celula = linha.insertCell();
                        celula.textContent = valor;
                    });
                });
            } catch (erro) {
                lista.replaceChildren();
                const linha = lista.insertRow();
                const celula = linha.insertCell();
                celula.colSpan = 3;
                celula.textContent = erro.message;
            }
        }

        formulario.addEventListener('submit', async (evento) => {
            evento.preventDefault();
            mensagem.textContent = 'Salvando...';

            const dados = {
                nome: document.getElementById('nome').value,
                empresa: document.getElementById('empresa').value
            };

            try {
                const resposta = await fetch('/api/unidades', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(dados)
                });

                const resultado = await resposta.json();

                if (!resposta.ok) {
                    const detalhes = resultado.errors
                        ? Object.values(resultado.errors).flat().join(' ')
                        : resultado.message;

                    throw new Error(detalhes || 'Não foi possível cadastrar.');
                }

                mensagem.textContent = 'Unidade cadastrada com sucesso!';
                formulario.reset();
                await carregarUnidades();
            } catch (erro) {
                mensagem.textContent = erro.message;
            }
        });

        carregarUnidades();
    </script>
</body>
</html>