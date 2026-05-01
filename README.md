# Controle de Atendimentos

Sistema em PHP puro com SQLite para controle de chamados internos, setores e prioridades.

## Funcionalidades

- cadastro de setores
- abertura de chamados com setor, prioridade e SLA em horas
- check-in do atendimento
- check-out do atendimento
- cancelamento de chamados com motivo registrado
- monitoramento com status, tempo total e destaque de atraso
- filtros por status no acompanhamento

## Requisitos

- PHP 8.x
- suporte a `pdo_sqlite`
- navegador web

## Como iniciar a aplicação

### No Laragon

1. Abra o Laragon.
2. Clique em `Start All`.
3. Abra o terminal dentro da pasta do projeto.
4. Execute:

```bash
php -S localhost:8000 -t public
```

5. Acesse no navegador:

```text
http://localhost:8000
```

### No Git Bash com Laragon

Se `php` não estiver no `PATH`, use o executável do Laragon:

```bash
/c/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe -S localhost:8000 -t public
```

### No CMD / PowerShell

Na raiz do projeto, execute:

```bash
php -S localhost:8000 -t public
```

Se o comando não for reconhecido, use o caminho completo do `php.exe`.

## Estrutura do projeto

```text
.
├─ .gitignore
├─ .htaccess
├─ README.md
├─ public/
│  ├─ index.php
│  └─ assets/
│     ├─ css/app.css
│     └─ js/app.js
├─ views/
│  ├─ layout.php
│  ├─ pages/
│  │  ├─ dashboard.php
│  │  ├─ monitor.php
│  │  ├─ sectors.php
│  │  └─ tickets.php
│  └─ partials/
│     ├─ cancel-modal.php
│     ├─ finish-modal.php
│     ├─ flash.php
│     ├─ sidebar.php
│     └─ topbar.php
├─ src/
│  ├─ Database.php
│  ├─ Repositories/
│  │  ├─ PriorityRepository.php
│  │  ├─ SectorRepository.php
│  │  └─ TicketRepository.php
│  └─ Support/
│     ├─ Flash.php
│     └─ Formatter.php
└─ database/
   ├─ app.sqlite
   ├─ schema.sql
   └─ seed.sql
```

## Telas

- `Dashboard`: resumo geral, chamados recentes e chamados em atraso.
- `Setores`: cadastro e listagem de setores.
- `Abrir Chamado`: abertura de chamados com setor, prioridade e SLA.
- `Acompanhamento`: check-in, check-out, cancelamento e filtros por status.

## Regras

- Todo chamado começa com status `Aberto`.
- O check-in só pode ser feito em chamados abertos.
- O check-out só pode ser feito em chamados em atendimento.
- O cancelamento só pode ser feito em chamados abertos ou em atendimento.
- Chamados em atraso são calculados com base na data de criação e no SLA da prioridade.
- O SLA fica salvo no próprio chamado, para não alterar chamados antigos quando uma prioridade muda.
- Chamados cancelados não entram na lista de ativos.

## Banco de dados

O banco local fica em:

```text
database/app.sqlite
```

Para reiniciar do zero:

1. Pare a aplicação.
2. Apague `database/app.sqlite`.
3. Abra a aplicação novamente.

## Teste rápido

1. Crie um setor.
2. Abra um chamado com prioridade e SLA.
3. Inicie o atendimento depois de um tempo.
4. Finalize o chamado.
5. Teste também o cancelamento.
6. Confira os filtros do monitor.

## Como testar atraso sem esperar

Para validar a regra de atraso rapidamente, altere o campo `created_at` de um chamado no banco SQLite para um horário anterior ao SLA da prioridade.

Exemplo:

```sql
UPDATE tickets
SET created_at = datetime('now', '-2 hours')
WHERE id = 1;
```

Depois, recarregue a aplicação. O chamado aparecerá como atrasado com base no tempo estimado da prioridade.

## Link online

- https://finl1n.alwaysdata.net/
