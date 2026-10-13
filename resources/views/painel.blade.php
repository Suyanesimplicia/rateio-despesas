<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel | Rateio de Despesas</title>

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

        header h1 {
            margin: 0;
        }

        main {
            max-width: 1100px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
        }

        .card h2 {
            font-size: 20px;
        }

        .card p {
            color: #64748b;
            line-height: 1.5;
        }
    </style>
</head>

<body>
    <header>
        <h1>Sistema de Rateio de Despesas</h1>
        <p>Gestão de despesas e distribuição por unidades</p>
    </header>

    <main>
        <h2>Painel de controle</h2>
        <p>Bem-vindo ao sistema de gerenciamento de despesas.</p>

        <div class="cards">
            <div class="card">
                <h2>Unidades</h2>
                <p>Cadastre e consulte as unidades das empresas.</p>
            </div>

            <div class="card">
                <h2>Despesas</h2>
                <p>Registre despesas e distribua os valores entre unidades.</p>
            </div>

            <div class="card">
                <h2>Importação CSV</h2>
                <p>Importe despesas em lote e consulte os erros encontrados.</p>
            </div>

            <div class="card">
                <h2>Relatórios</h2>
                <p>Consulte os totais por unidade e período.</p>
            </div>
        </div>
    </main>
</body>
</html>