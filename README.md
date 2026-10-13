# Sistema de Rateio de Despesas entre Unidades

Sistema web desenvolvido em *PHP 8.3, Laravel 13 e MySQL*, destinado ao gerenciamento de despesas compartilhadas entre diferentes unidades de uma organização.

A aplicação permite cadastrar unidades empresariais, registrar despesas, definir percentuais de rateio, realizar conversões de valores em dólar para reais, importar registros por arquivos CSV e gerar relatórios financeiros consolidados.

O projeto foi desenvolvido como parte de uma avaliação técnica, com foco na implementação de regras de negócio, organização do código, persistência de dados, validação de informações e tratamento de erros.

---

## 1. Visão geral do projeto

Em organizações que possuem diferentes unidades ou centros de custo, determinadas despesas precisam ser compartilhadas entre mais de uma unidade.

Por exemplo, uma despesa de R$ 1.000,00 referente a um serviço utilizado por duas unidades pode ser distribuída da seguinte forma:

| Unidade | Percentual | Valor atribuído |
|---|---:|---:|
| Unidade A | 60% | R$ 600,00 |
| Unidade B | 40% | R$ 400,00 |
| *Total* | *100%* | *R$ 1.000,00* |

O sistema automatiza esse processo, evitando cálculos manuais e garantindo que a soma dos valores distribuídos corresponda ao valor total da despesa.

Além disso, a aplicação oferece suporte a despesas em moedas diferentes, centralizando os resultados financeiros em reais para facilitar a consulta e a consolidação dos dados.

### Objetivos principais

- Centralizar o cadastro de unidades e despesas.
- Automatizar a distribuição de despesas por percentual.
- Validar as regras financeiras antes de salvar os registros.
- Evitar divergências causadas por arredondamento monetário.
- Permitir o registro de despesas em BRL e USD.
- Consultar a cotação histórica para conversão de dólares.
- Facilitar o cadastro de despesas por importação CSV.
- Disponibilizar relatórios de despesas por unidade e período.
- Expor as funcionalidades por meio de uma API REST.

---

## 2. Tecnologias utilizadas

| Tecnologia | Aplicação no projeto |
|---|---|
| PHP 8.3 | Linguagem utilizada no backend |
| Laravel 13 | Framework responsável pela aplicação |
| MySQL | Armazenamento dos dados |
| Eloquent ORM | Acesso ao banco e relacionamentos |
| Laravel Blade | Construção das páginas web |
| HTML, CSS e JavaScript | Estrutura, apresentação e interações da interface |
| API REST | Comunicação por endpoints HTTP |
| PHPUnit / Laravel Test | Execução de testes automatizados |
| Composer | Gerenciamento das dependências PHP |
| Git | Versionamento do código |
| Postman | Testes manuais dos endpoints |

### Por que Laravel?

O Laravel foi utilizado por oferecer recursos que ajudam a manter o projeto organizado, incluindo:

- Roteamento de requisições HTTP.
- Controllers para organizar as operações da API.
- Models para representar as entidades do sistema.
- Migrations para estruturar o banco de dados.
- Validação de dados recebidos.
- Transações para operações que precisam ser concluídas em conjunto.
- Estrutura de testes automatizados.

---

## 3. Arquitetura e organização

A aplicação segue a estrutura do Laravel, separando as responsabilidades de acesso aos dados, processamento de requisições e execução das regras de negócio.

### Controllers

Os controllers recebem as requisições, validam os dados necessários e coordenam as operações.

Entre os principais controllers da API estão:

- UnidadeController: operações relacionadas ao cadastro de unidades.
- DespesaController: cadastro, consulta, atualização e exclusão de despesas.
- ImportacaoController: processamento das importações CSV.
- RelatorioController: consulta dos totais financeiros por unidade.

### Models

Os models representam as entidades armazenadas no banco:

- Unidade: unidade empresarial participante do rateio.
- Despesa: informações da despesa, incluindo valor, moeda e conversão.
- Rateio: distribuição do valor de uma despesa entre unidades.

### Services

As regras que exigem processamento específico foram separadas em serviços.

*RateioService*

Responsável por calcular os valores destinados a cada unidade, validar a soma dos percentuais e tratar os centavos resultantes da divisão.

*CotacaoService*

Responsável pela consulta da cotação histórica utilizada na conversão de despesas em dólar para reais.

*ImportacaoCsvService*

Responsável pela leitura e validação estrutural dos arquivos CSV utilizados na importação.

Essa divisão permite que regras importantes sejam reutilizadas e testadas independentemente das requisições HTTP.

### Estrutura simplificada

text
rateio-despesas/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── API/
│   │           ├── UnidadeController.php
│   │           ├── DespesaController.php
│   │           ├── ImportacaoController.php
│   │           └── RelatorioController.php
│   ├── Models/
│   │   ├── Unidade.php
│   │   ├── Despesa.php
│   │   └── Rateio.php
│   └── Services/
│       ├── RateioService.php
│       ├── CotacaoService.php
│       └── ImportacaoCsvService.php
├── database/
│   └── migrations/
├── resources/
│   └── views/
├── routes/
│   ├── api.php
│   └── web.php
├── tests/
│   ├── Unit/
│   └── Feature/
├── .env.example
├── composer.json
└── README.md


---

## 4. Funcionalidades implementadas

### 4.1. Gerenciamento de unidades

O sistema permite gerenciar as unidades que participam da distribuição das despesas.

Cada unidade possui informações de identificação, incluindo nome e empresa.

As operações disponíveis são:

- Cadastrar uma unidade.
- Consultar a lista de unidades.
- Consultar uma unidade específica.
- Atualizar os dados de uma unidade.
- Excluir uma unidade.

As unidades são utilizadas como referência nos rateios cadastrados.

### 4.2. Gerenciamento de despesas

Cada despesa contém as seguintes informações principais:

| Campo | Descrição |
|---|---|
| Descrição | Identificação da despesa |
| Fornecedor | Responsável pelo fornecimento do produto ou serviço |
| Data | Data de referência da despesa |
| Valor | Valor original informado |
| Moeda | BRL ou USD |
| Valor em BRL | Valor consolidado em reais |
| Cotação | Taxa utilizada na conversão |
| Status de conversão | Situação do processamento da conversão |
| Rateios | Distribuição entre as unidades |

O sistema permite criar, consultar, atualizar e excluir despesas.

Ao cadastrar ou atualizar uma despesa, o backend valida os dados, calcula os valores e persiste os registros relacionados.

### 4.3. Distribuição por percentual

Uma despesa pode ser distribuída entre diferentes unidades, conforme percentuais informados.

Exemplo:

Despesa: R$ 2.500,00.

| Unidade | Percentual | Valor |
|---|---:|---:|
| Unidade A | 50% | R$ 1.250,00 |
| Unidade B | 30% | R$ 750,00 |
| Unidade C | 20% | R$ 500,00 |
| *Total* | *100%* | *R$ 2.500,00* |

A aplicação exige que os percentuais somem exatamente 100%.

Também impede que a mesma unidade seja informada mais de uma vez no rateio de uma mesma despesa.

### 4.4. Tratamento de arredondamento

Um dos pontos importantes do projeto é o tratamento de valores monetários que não podem ser divididos igualmente.

Considere uma despesa de R$ 100,00 distribuída entre três unidades:

| Unidade | Percentual | Valor final |
|---|---:|---:|
| Unidade A | 33,33% | R$ 33,33 |
| Unidade B | 33,33% | R$ 33,33 |
| Unidade C | 33,34% | R$ 33,34 |
| *Total* | *100%* | *R$ 100,00* |

O cálculo utiliza valores em centavos para preservar a consistência da distribuição.

Quando necessário, a diferença residual é ajustada no último rateio.

Dessa maneira, o total distribuído corresponde ao valor da despesa.

### 4.5. Conversão de USD para BRL

O sistema aceita despesas nas moedas:

- BRL: real brasileiro.
- USD: dólar americano.

Para despesas em reais, o valor consolidado permanece igual ao valor informado, utilizando cotação igual a 1.

Para despesas em dólar, o sistema consulta uma cotação histórica por meio do serviço de cotação integrado à aplicação.

O valor convertido é calculado a partir da multiplicação:

*Valor em BRL = Valor em USD × Cotação USD/BRL*

Exemplo ilustrativo:

| Informação | Valor |
|---|---:|
| Despesa original | USD 100,00 |
| Cotação hipotética | R$ 5,20 |
| Valor convertido | R$ 520,00 |

A cotação apresentada nesse exemplo é apenas demonstrativa.

O valor real depende da consulta correspondente à data da despesa.

#### Falha na consulta da cotação

Caso a consulta à cotação histórica falhe, o cadastro ou a atualização de uma despesa em USD preserva os dados informados, sem utilizar uma taxa arbitrária.

A despesa é armazenada com status_conversao = pendente, enquanto os campos cotacao e valor_brl permanecem nulos. Os percentuais de rateio são validados e preservados, mas seus valores em reais também permanecem nulos.

A API retorna a despesa cadastrada ou atualizada, permitindo identificar os registros que precisam de conversão posterior.

Somente despesas com status_conversao = convertido entram nos totais dos relatórios financeiros.

*Limitação:* ainda não existe uma rotina automática de reprocessamento das despesas pendentes. Essa funcionalidade poderá ser implementada com filas e tentativas posteriores de consulta à cotação.

---

## 5. Importação de despesas por CSV

A aplicação permite importar despesas por arquivos CSV, reduzindo a necessidade de cadastro individual.

O processamento contempla:

1. Recebimento do arquivo.
2. Leitura do conteúdo CSV.
3. Verificação do cabeçalho esperado.
4. Identificação das linhas do arquivo.
5. Validação e processamento individual dos registros.
6. Contabilização dos registros aceitos e rejeitados.
7. Apresentação dos erros encontrados.

### Processamento parcial

A importação foi estruturada para permitir que um arquivo contendo linhas válidas e inválidas seja processado parcialmente.

Por exemplo, um arquivo com três despesas pode apresentar:

| Linha | Situação |
|---|---|
| 2 | Aceita |
| 3 | Rejeitada por inconsistência |
| 4 | Aceita |

Nesse cenário, duas despesas são processadas e uma é rejeitada.

O retorno permite identificar quantos registros foram aceitos e quais apresentaram problemas.

### Validações do CSV

O serviço de leitura contempla a verificação do cabeçalho e o tratamento de arquivos com marcação BOM UTF-8.

O projeto utiliza arquivos de exemplo para testar o comportamento da importação, incluindo um cenário com registros mistos.

*Observação:* para importar dados corretamente, utilize o formato e o cabeçalho dos arquivos de exemplo incluídos no repositório.

---

## 6. Relatórios financeiros

O sistema disponibiliza uma funcionalidade de relatório para consultar os valores atribuídos a cada unidade.

Os relatórios consolidam os valores dos rateios em reais, independentemente da moeda original da despesa.

Isso permite comparar despesas originalmente cadastradas em BRL e USD utilizando uma mesma moeda de referência.

### Consulta por período

O endpoint de relatórios permite filtrar os resultados por intervalo de datas.

Exemplo ilustrativo:

| Unidade | Total em BRL |
|---|---:|
| Unidade A | R$ 3.500,00 |
| Unidade B | R$ 2.150,00 |
| Unidade C | R$ 1.350,00 |
| *Total* | *R$ 7.000,00* |

Os valores acima são demonstrativos e não representam necessariamente dados existentes no banco.

---

## 7. Interface web

Além da API, o projeto possui uma interface web construída com Laravel Blade, HTML e JavaScript.

A página inicial funciona como um painel de navegação para as principais funcionalidades.

### Páginas disponíveis

| Caminho | Funcionalidade |
|---|---|
| / | Painel principal |
| /unidades | Gerenciamento de unidades |
| /despesas | Gerenciamento de despesas |
| /importacao | Importação CSV |
| /relatorios | Consulta de relatórios |

A interface permite utilizar as funcionalidades principais sem a necessidade de executar manualmente todas as requisições pelo Postman.

---

## 8. Endpoints da API REST

As rotas da API utilizam o prefixo /api.

### 8.1. Unidades

| Método | Endpoint | Operação |
|---|---|---|
| GET | /api/unidades | Listar unidades |
| POST | /api/unidades | Criar unidade |
| GET | /api/unidades/{id} | Consultar unidade |
| PUT | /api/unidades/{id} | Atualizar unidade |
| DELETE | /api/unidades/{id} | Excluir unidade |

### 8.2. Despesas

| Método | Endpoint | Operação |
|---|---|---|
| GET | /api/despesas | Listar despesas |
| POST | /api/despesas | Criar despesa |
| GET | /api/despesas/{id} | Consultar despesa |
| PUT | /api/despesas/{id} | Atualizar despesa |
| DELETE | /api/despesas/{id} | Excluir despesa |

### 8.3. Importação

POST /api/despesas/importar

Recebe um arquivo CSV para processamento.

### 8.4. Relatórios

GET /api/relatorios/unidades

Retorna informações consolidadas dos valores distribuídos entre as unidades, com suporte aos filtros de período implementados.

---

## 9. Exemplo de utilização da API

### Criar uma despesa

*Método:* POST

*URL:*

text
http://127.0.0.1:8000/api/despesas


*Body — JSON:*

json
{
  "descricao": "Manutenção de equipamentos",
  "fornecedor": "Empresa Exemplo",
  "data": "2026-10-10",
  "valor": 250.00,
  "moeda": "BRL",
  "rateios": [
    {
      "unidade_id": 1,
      "percentual": 60
    },
    {
      "unidade_id": 2,
      "percentual": 40
    }
  ]
}


### Resultado esperado do cálculo

| Unidade | Percentual | Valor em BRL |
|---|---:|---:|
| ID 1 | 60% | R$ 150,00 |
| ID 2 | 40% | R$ 100,00 |

As unidades de IDs 1 e 2 devem existir previamente no banco de dados.

Em caso de sucesso, o cadastro retorna HTTP 201 Created, incluindo os dados da despesa e os rateios gerados.

### Atualizar uma despesa

*Método:* PUT

*URL:*

text
http://127.0.0.1:8000/api/despesas/11


O corpo da requisição segue a estrutura de cadastro, com os campos obrigatórios e a distribuição atualizada.

O ID 11 foi utilizado durante os testes manuais e não representa um registro permanente.

### Excluir uma despesa

*Método:* DELETE

*URL:*

text
http://127.0.0.1:8000/api/despesas/{id}


Resposta de sucesso:

json
{
  "message": "Despesa excluída com sucesso."
}


---

## 10. Validações e tratamento de erros

O backend utiliza validações para impedir o armazenamento de informações inconsistentes.

Entre as regras aplicadas ao cadastro e à atualização de despesas estão:

| Campo | Validação |
|---|---|
| Descrição | Obrigatória, texto de até 255 caracteres |
| Fornecedor | Obrigatório, texto de até 255 caracteres |
| Data | Data válida, não posterior ao dia atual |
| Valor | Numérico, positivo e com até duas casas decimais |
| Moeda | Apenas BRL ou USD |
| Rateios | Pelo menos um rateio |
| Unidade | Identificador inteiro e existente |
| Percentual | Valor positivo, limitado a 100, com até quatro casas decimais |
| Soma dos percentuais | Exatamente 100% |

Também são tratados cenários como:

- Unidade inexistente.
- Despesa inexistente.
- Percentuais inconsistentes.
- Repetição de uma unidade no mesmo rateio.
- Falha na consulta da cotação.
- Arquivo CSV com cabeçalho inválido.
- Registros inválidos durante a importação.

As respostas seguem os mecanismos de validação e tratamento de erros da aplicação Laravel.

---

## 11. Persistência e integridade dos dados

O sistema utiliza MySQL para armazenar as informações.

### Relacionamentos

Uma despesa pode possuir vários rateios.

Cada rateio associa uma despesa a uma unidade e registra o percentual e o valor correspondente em reais.

Representação conceitual:

text
UNIDADE
   |
   | 1
   |
   | N
 RATEIO
   |
   | N
   |
   | 1
DESPESA


O rateio funciona como o vínculo entre a unidade e a despesa.

### Transações

As operações de criação, atualização e exclusão de despesas utilizam DB::transaction().

Isso permite agrupar as alterações relacionadas à despesa e aos seus rateios em uma única transação.

Na atualização, por exemplo, o sistema:

1. Valida os novos dados.
2. Calcula os valores atualizados.
3. Atualiza a despesa.
4. Remove os rateios anteriores.
5. Cria os novos rateios.
6. Conclui a transação.

Caso ocorra uma exceção durante a transação, as alterações nela realizadas são revertidas pelo banco de dados.

---

## 12. Instalação e configuração

### 12.1. Pré-requisitos

Para executar o projeto localmente, é necessário ter:

- PHP compatível com o projeto.
- Composer.
- MySQL.
- Git.
- Extensões PHP exigidas pelas dependências do Laravel.

O desenvolvimento foi realizado utilizando PHP 8.3 e Laravel 13.

### 12.2. Clonar o repositório

bash
git clone URL_DO_REPOSITORIO
cd rateio-despesas


Substitua URL_DO_REPOSITORIO pelo endereço do repositório publicado no GitHub.

### 12.3. Instalar dependências

bash
composer install


### 12.4. Configurar o ambiente

Crie o arquivo .env:

bash
cp .env.example .env


Gere a chave da aplicação:

bash
php artisan key:generate


### 12.5. Configurar o banco de dados

Crie um banco MySQL para o projeto.

Exemplo:

sql
CREATE DATABASE rateio_despesas
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;


Configure o arquivo .env:

env
APP_NAME="Rateio de Despesas"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rateio_despesas
DB_USERNAME=SEU_USUARIO
DB_PASSWORD=SUA_SENHA


As credenciais devem ser substituídas pelos dados do ambiente local.

*Nunca publique senhas reais, tokens ou o arquivo .env no GitHub.*

### 12.6. Executar as migrações

bash
php artisan migrate


Esse comando aplica as migrations do projeto ao banco configurado.

### 12.7. Iniciar a aplicação

bash
php artisan serve


O endereço padrão será:

http://127.0.0.1:8000

A API estará disponível a partir de:

http://127.0.0.1:8000/api

---

## 13. Testes automatizados

O projeto possui testes automatizados utilizando a estrutura de testes do Laravel.

Para executar:

bash
php artisan test


### Testes do serviço de rateio

Os testes verificam:

- Distribuição entre duas unidades.
- Tratamento de centavos sem perda do valor total.
- Rejeição de percentuais cuja soma não seja 100%.
- Rejeição de um rateio vazio.

### Testes do serviço de importação

Os testes verificam:

- Leitura de CSV contendo duas despesas.
- Reconhecimento de cabeçalho com BOM UTF-8.
- Rejeição de cabeçalho inválido.

### Teste de indisponibilidade da cotação

O teste funcional DespesaCotacaoPendenteTest simula uma falha
no serviço externo de cotação e verifica se a despesa em USD
é preservada no banco de dados com status pendente,
sem cotação ou valor convertido em reais.

### Testes básicos da aplicação

Também são executados os testes padrão de unidade e funcionalidade do Laravel.

### Resultado registrado

Na última execução durante o desenvolvimento:

| Indicador | Resultado |
|---|---:|
| Testes executados | 10 |
| Testes aprovados | 10 |
| Asserções | 37 |
| Falhas | 0 |

Esses resultados correspondem aos testes existentes naquele momento, não a uma cobertura integral de todos os endpoints.

---

## 14. Testes manuais realizados

Além dos testes automatizados, foram realizadas verificações manuais utilizando o Postman e a interface web.

### Atualização de despesa

Foi criada uma despesa temporária com valor original de R$ 100,00.

Posteriormente, a despesa foi atualizada para R$ 250,00, com a seguinte distribuição:

| Unidade | Percentual | Valor |
|---|---:|---:|
| ID 1 | 60% | R$ 150,00 |
| ID 2 | 40% | R$ 100,00 |

A resposta da API confirmou a atualização dos dados e a geração dos novos rateios.

### Exclusão de despesa

A despesa temporária foi excluída por meio do endpoint DELETE.

A API retornou uma mensagem confirmando a exclusão.

### Importação parcial

Foi verificado um cenário de importação contendo registros válidos e inválidos.

O processamento apresentou dois registros aceitos e um rejeitado.

### Relatórios e navegação

As páginas de importação e relatórios foram utilizadas durante a verificação funcional da aplicação.

---

## 15. Decisões técnicas

Algumas decisões foram adotadas para manter o comportamento do sistema consistente.

*Separação das regras de negócio*

O cálculo do rateio, a consulta de cotação e a leitura CSV foram organizados em serviços específicos, evitando concentrar todo o processamento nos controllers.

*Precisão monetária*

Os cálculos de distribuição utilizam centavos para reduzir divergências decorrentes de arredondamento.

*Validação antes da persistência*

Os dados são validados antes da criação ou atualização de despesas, evitando registros com percentuais inconsistentes.

*Uso de transações*

As alterações relacionadas a despesas e rateios são executadas de maneira agrupada, contribuindo para a integridade das informações.

*Tratamento de indisponibilidade externa*

Em caso de falha na consulta da cotação, as operações individuais de cadastro e atualização preservam a despesa em USD com status pendente, mantendo os percentuais de rateio e deixando os valores em BRL nulos. Nenhuma cotação arbitrária é utilizada.

*Importação com identificação de erros*

O processamento por linha permite identificar registros rejeitados sem necessariamente descartar todo o arquivo.

---

## 16. Limitações e possibilidades de evolução

O projeto atende ao escopo funcional desenvolvido para a avaliação técnica, mas possui oportunidades de evolução.

Entre as melhorias futuras estão:

- Ampliação da cobertura de testes automatizados para os controllers e endpoints.
- Implementação de autenticação e autorização de usuários.
- Paginação e filtros adicionais nas listagens.
- Exportação de relatórios em CSV ou PDF.
- Melhoria da experiência visual e responsividade da interface.
- Registro de logs mais detalhados para importações e falhas externas.
- Documentação interativa da API com OpenAPI/Swagger.
- Automatização de testes e validações por integração contínua.
- Reprocessamento automático das despesas com conversão pendente, utilizando filas e tentativas programadas.
- Ampliação dos testes de integração para importação CSV, conversão cambial e relatórios financeiros.

### Decisões de escopo

O processamento automático de despesas pendentes, a autenticação da API e a infraestrutura com Docker não foram incluídos nesta entrega para priorizar as funcionalidades centrais do desafio técnico dentro do prazo.

O sistema preserva as despesas em USD quando a consulta cambial falha, mas o reprocessamento dessas despesas permanece como evolução futura.

### Perguntas para o negócio antes da implantação em produção

1. Qual regra de cotação deve ser utilizada: PTAX de venda, compra ou outra referência?
2. Para fins de semana e feriados, devemos utilizar a última cotação disponível do dia útil anterior?
3. Como devem ser tratadas despesas pendentes: reprocessamento automático, intervenção manual ou ambos?
4. Qual prazo máximo é aceitável para regularizar uma despesa sem cotação?
5. Quem poderá cadastrar, editar, excluir e consultar despesas e relatórios?
6. Despesas pendentes devem aparecer nos relatórios em uma seção separada, além de serem excluídas dos totais em BRL?
7. Como devem ser tratados lançamentos duplicados durante a importação CSV?
8. É necessário manter histórico de alterações e trilha de auditoria das despesas?
9. Existem limites de valor, períodos de fechamento financeiro ou regras de aprovação?
10. Qual volume esperado de unidades, despesas e importações por mês?

Esses itens representam sugestões de evolução e não funcionalidades já implementadas.

---

## 17. Considerações finais

O Sistema de Rateio de Despesas foi desenvolvido para resolver um problema comum na gestão financeira: a distribuição consistente de custos entre diferentes unidades de uma organização.

O projeto reúne operações CRUD, API REST, banco de dados relacional, cálculos financeiros, integração com serviço externo, importação CSV, relatórios e testes automatizados.

Durante o desenvolvimento, foram priorizadas a clareza das regras de negócio, a organização das responsabilidades e a prevenção de inconsistências nos dados.

A aplicação demonstra a utilização prática de PHP e Laravel na construção de uma solução backend integrada a uma interface web, com atenção à validação de dados e à integridade das operações.