# W5i - Controle de Atendimentos

Aplicação em PHP puro com SQLite para gerenciar chamados internos, setores e prioridades.

## Como executar localmente

1. Instale o PHP com suporte a `pdo_sqlite`.
2. Abra o terminal na raiz do projeto.
3. Execute:

```bash
php -S localhost:8000 -t public
```

4. Acesse `http://localhost:8000`.

## Estrutura do projeto

```text
public/
  index.php              # Front controller
  assets/
    css/app.css          # Estilo da aplicação
    js/app.js            # Interação da interface
views/
  layout.php             # Estrutura base da página
  partials/              # Componentes compartilhados
  pages/                 # Conteúdo de cada tela
src/
  Database.php           # Conexão SQLite
  Repositories/          # Regras de acesso aos dados
  Support/               # Utilitários
database/
  schema.sql             # Criação das tabelas
  seed.sql               # Dados iniciais
```

## Telas

- `Dashboard`: visão geral com histórico e distribuição dos chamados.
- `Setores`: cadastro e listagem dos setores.
- `Prioridades`: cadastro de prioridades com SLA em horas.
- `Abrir Chamado`: criação de novos chamados.
- `Acompanhamento`: check-in, check-out e monitoramento dos chamados.

## Regras principais

- Todo chamado nasce com status `Aberto`.
- O check-in só pode ser feito em chamados abertos.
- O check-out só pode ser feito em chamados em atendimento.
- A listagem destaca chamados que ultrapassam o SLA da prioridade.
- O banco é criado automaticamente na primeira execução, caso não exista.
- É possível excluir setores e prioridades não utilizados para corrigir cadastros errados.
- Chamados são mantidos como histórico e não podem ser excluídos.
- A aplicação mostra mensagens de erro quando alguma regra é violada.

## Banco de dados

O arquivo principal do banco é `database/app.sqlite`.

Se quiser zerar os dados para testar do zero:

1. Pare o servidor local.
2. Apague o arquivo `database/app.sqlite`.
3. Abra a aplicação novamente.

## Observação

A aplicação foi pensada para ficar clara para avaliação técnica:

- HTML separado da lógica
- CSS separado em arquivo próprio
- JavaScript separado em arquivo próprio
- páginas divididas por responsabilidade
