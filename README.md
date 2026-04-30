# Controle de Atendimentos

Aplicação em PHP puro com SQLite para gerenciar chamados de suporte e solicitações internas.

Versão online:
- https://finl1n.alwaysdata.net/

## O que este projeto entrega

O sistema foi organizado para cobrir os requisitos do desafio e deixar a operação fácil de entender:

- cadastro de setores
- abertura de chamados com vínculo ao setor, prioridade e SLA em horas
- check-in e check-out com controle de status
- registro de solução, responsável e motivo de atraso
- destaque visual para chamados fora do prazo
- histórico separado por perfil de uso
- versão responsiva para desktop e mobile

## Como cada requisito foi atendido

- **Cadastro de Setores**: disponível na área de setores.
- **Cadastro de Prioridades com tempo estimado**: o nível e o SLA são informados na abertura do chamado.
- **Cadastro do Chamado**: o chamado nasce com status `Aberto`, vinculado ao setor e ao nível de prioridade.
- **Atendimento (Check-in)**: registra data e hora de início, somente para chamados ainda válidos.
- **Finalização (Check-out)**: registra data/hora de término e a solução aplicada.
- **Listagem dos chamados**: mostra setor, prioridade, status atual, tempo total e histórico do atendimento.
- **Destaque de SLA**: chamados abertos ou em atendimento que ultrapassam o prazo recebem destaque e pedem justificativa quando necessário.

## Fluxo por perfil

Ao entrar no sistema, a pessoa escolhe um perfil:

- `Solicitante`
  - abre chamado
  - vê seus próprios chamados
  - acompanha o andamento usando o mesmo nome informado na entrada

- `Responsável`
  - acompanha a fila completa
  - inicia atendimento
  - finaliza chamados
  - registra solução e justificativa de atraso quando houver

Observação importante:

- O solicitante deve usar sempre o mesmo nome para visualizar o histórico salvo.
- O responsável não depende do mesmo nome para operar a fila.

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
  index.php              # Entrada principal da aplicação
  assets/
    css/app.css          # Estilos da interface
    js/app.js            # Interações da interface
views/
  layout.php             # Estrutura base da página
  pages/                 # Telas separadas por funcionalidade
  partials/              # Componentes reutilizáveis
src/
  Database.php           # Conexão com SQLite
  Repositories/          # Acesso aos dados
  Support/               # Funções auxiliares
database/
  schema.sql             # Criação das tabelas
  seed.sql               # Base inicial vazia
```

## Telas principais

- `Escolha de perfil`: define se a pessoa atua como solicitante ou responsável.
- `Dashboard`: resumo operacional, chamados recentes, atrasos e fechamentos.
- `Setores`: cadastro e listagem de setores.
- `Abrir Chamado`: criação de chamados com setor, prioridade e SLA.
- `Acompanhamento`: check-in, check-out e histórico de atendimento.

## Banco de dados

O banco local fica em:

```text
database/app.sqlite
```

Para testar do zero:

1. Pare o servidor local.
2. Apague o arquivo `database/app.sqlite`.
3. Abra a aplicação novamente.

## Interface

- A interface foi pensada para funcionar bem em desktop e mobile.
- O JavaScript é usado apenas para pequenos detalhes de interação, como o stepper de horas e o modal de encerramento.

## Estrutura de entrega

O projeto pode ser revisado diretamente no repositório e testado online no endereço acima.  
A organização de pastas foi mantida para facilitar manutenção, leitura e avaliação.
