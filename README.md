# Controle de Atendimentos

Projeto em PHP puro com SQLite para controle de chamados internos, setores e prioridades.

## Visao geral

O sistema foi desenvolvido para atender ao desafio tecnico com foco em:

- cadastro de setores
- cadastro de prioridades com tempo estimado em horas
- abertura de chamados vinculando setor e prioridade
- check-in e check-out do atendimento
- listagem com status, setor, prioridade e tempo total
- destaque visual para chamados que ultrapassam o SLA

## Como rodar localmente

1. Instale o PHP com suporte a `pdo_sqlite`.
2. Abra o terminal na raiz do projeto.
3. Execute:

```bash
php -S localhost:8000 -t public
```

4. Abra no navegador:

```text
http://localhost:8000
```

## Estrutura do projeto

```text
public/
  index.php              # Entrada principal da aplicacao
  assets/
    css/app.css          # Estilos da interface
    js/app.js            # Interacoes da interface
views/
  layout.php             # Estrutura base da pagina
  partials/              # Componentes reutilizaveis
  pages/                 # Telas separadas por funcionalidade
src/
  Database.php           # Conexao com SQLite
  Repositories/          # Acesso aos dados
  Support/               # Funcoes auxiliares
database/
  schema.sql             # Criacao das tabelas
  seed.sql               # Dados iniciais
```

## Telas

- `Dashboard`: resumo geral, historico recente e chamados em atraso.
- `Setores`: cadastro e listagem de setores.
- `Prioridades`: cadastro de prioridades com SLA.
- `Abrir Chamado`: criacao de novos chamados.
- `Acompanhamento`: check-in, check-out e monitoramento dos chamados.

## Regras do sistema

- Todo chamado nasce com status `Aberto`.
- O check-in so pode ser feito em chamados abertos.
- O check-out so pode ser feito em chamados em atendimento.
- A listagem destaca chamados que ultrapassam o tempo estimado da prioridade.
- Chamados nao podem ser excluidos, para manter o historico.
- Setores e prioridades podem ser excluidos apenas quando nao estiverem sendo usados.
- Mensagens de erro e sucesso aparecem na propria interface.

## Banco de dados

O banco fica em:

```text
database/app.sqlite
```

Se quiser testar do zero:

1. Pare o servidor local.
2. Apague o arquivo `database/app.sqlite`.
3. Abra a aplicacao novamente.

## Link online

Versao online do projeto:

- https://finl1n.alwaysdata.net/
