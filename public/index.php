<?php

declare(strict_types=1);

session_start();
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Support/Flash.php';
require_once __DIR__ . '/../src/Support/Formatter.php';
require_once __DIR__ . '/../src/Repositories/SectorRepository.php';
require_once __DIR__ . '/../src/Repositories/PriorityRepository.php';
require_once __DIR__ . '/../src/Repositories/TicketRepository.php';

$database = new Database(__DIR__ . '/../database/app.sqlite');
$pdo = $database->pdo();

$sectorRepository = new SectorRepository($pdo);
$priorityRepository = new PriorityRepository($pdo);
$ticketRepository = new TicketRepository($pdo);

ensureSchema($pdo);
ensureTicketColumns($pdo);

$page = $_GET['page'] ?? 'dashboard';
if (!in_array($page, ['dashboard', 'sectors', 'tickets', 'monitor'], true)) {
    $page = 'dashboard';
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;

try {
    handleAction($action, $sectorRepository, $priorityRepository, $ticketRepository);
} catch (Throwable $throwable) {
    Flash::set('error', $throwable->getMessage());
}

$flash = Flash::get();
$sectors = $sectorRepository->all();
$tickets = $ticketRepository->allWithRelations();
$summary = summarizeTickets($tickets);
$monitorStatusFilter = $_GET['status'] ?? 'all';
if (!in_array($monitorStatusFilter, ['all', 'open', 'overdue', 'progress', 'finished', 'active'], true)) {
    $monitorStatusFilter = 'all';
}
$monitorTickets = sortTicketsForMonitor(filterTicketsByStatus($tickets, $monitorStatusFilter));
$monitorOverdueTickets = sortTicketsForMonitor(array_values(array_filter($tickets, static fn (array $ticket): bool => ticketIsOverdue($ticket))));

function ensureSchema(PDO $pdo): void
{
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('sectors', 'priorities', 'tickets')")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) === 3) {
        return;
    }

    $pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
    $pdo->exec(file_get_contents(__DIR__ . '/../database/seed.sql'));
}

function ensureTicketColumns(PDO $pdo): void
{
    $columns = $pdo->query('PRAGMA table_info(tickets)')->fetchAll();
    $existing = array_column($columns, 'name');

    $required = [
        'protocol_number' => 'ALTER TABLE tickets ADD COLUMN protocol_number INTEGER NULL',
        'requester_name' => 'ALTER TABLE tickets ADD COLUMN requester_name TEXT NULL',
        'responder_name' => 'ALTER TABLE tickets ADD COLUMN responder_name TEXT NULL',
        'delay_reason' => 'ALTER TABLE tickets ADD COLUMN delay_reason TEXT NULL',
        'estimated_hours' => 'ALTER TABLE tickets ADD COLUMN estimated_hours INTEGER NULL',
        'canceled_at' => 'ALTER TABLE tickets ADD COLUMN canceled_at TEXT NULL',
        'canceled_by' => 'ALTER TABLE tickets ADD COLUMN canceled_by TEXT NULL',
        'cancel_reason' => 'ALTER TABLE tickets ADD COLUMN cancel_reason TEXT NULL',
    ];

    foreach ($required as $column => $sql) {
        if (!in_array($column, $existing, true)) {
            $pdo->exec($sql);
        }
    }

    $pdo->exec('UPDATE tickets SET protocol_number = id WHERE protocol_number IS NULL');
    $pdo->exec("UPDATE tickets SET requester_name = 'Solicitante não informado' WHERE requester_name IS NULL OR requester_name = ''");
    $pdo->exec('
        UPDATE tickets
        SET estimated_hours = COALESCE(
            estimated_hours,
            (
                SELECT priorities.estimated_hours
                FROM priorities
                WHERE priorities.id = tickets.priority_id
            ),
            1
        )
    ');
    $pdo->exec('UPDATE tickets SET estimated_hours = 1 WHERE estimated_hours IS NULL OR estimated_hours <= 0');
}

function handleAction(?string $action, SectorRepository $sectorRepository, PriorityRepository $priorityRepository, TicketRepository $ticketRepository): void
{
    if ($action === null || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    switch ($action) {
        case 'create_sector':
            $name = trim((string) ($_POST['name'] ?? ''));
            if ($name === '') {
                throw new RuntimeException('Informe o nome do setor.');
            }

            if ($sectorRepository->existsByName($name)) {
                throw new RuntimeException('Esse setor já existe.');
            }

            $sectorRepository->create($name);
            Flash::set('success', 'Setor cadastrado com sucesso.');
            break;

        case 'delete_sector':
            $sectorId = requireInt('sector_id', 'Setor inválido.');
            if ($sectorRepository->countTickets($sectorId) > 0) {
                throw new RuntimeException('Não é possível excluir um setor que já possui chamados.');
            }

            $sectorRepository->delete($sectorId);
            Flash::set('success', 'Setor removido com sucesso.');
            break;

        case 'create_ticket':
            $sectorId = filter_var($_POST['sector_id'] ?? null, FILTER_VALIDATE_INT);
            $priorityName = trim((string) ($_POST['priority_name'] ?? ''));
            $estimatedHours = filter_var($_POST['estimated_hours'] ?? null, FILTER_VALIDATE_INT);
            $requesterName = trim((string) ($_POST['requester_name'] ?? ''));
            $title = trim((string) ($_POST['title'] ?? ''));
            $description = trim((string) ($_POST['description'] ?? ''));

            if ($sectorId === false) {
                throw new RuntimeException('Selecione o setor.');
            }

            if (!$sectorRepository->exists((int) $sectorId)) {
                throw new RuntimeException('O setor selecionado não existe.');
            }

            if (!in_array($priorityName, ['Baixa', 'Média', 'Alta'], true)) {
                throw new RuntimeException('Selecione um nível de prioridade válido.');
            }

            if ($estimatedHours === false || $estimatedHours <= 0) {
                throw new RuntimeException('Informe um tempo estimado válido em horas.');
            }

            if ($requesterName === '') {
                throw new RuntimeException('Informe o nome de quem solicitou o chamado.');
            }

            if ($title === '') {
                throw new RuntimeException('Informe o título do chamado.');
            }

            $priorityId = $priorityRepository->upsert($priorityName, (int) $estimatedHours);
            $ticketId = $ticketRepository->create((int) $sectorId, $priorityId, (int) $estimatedHours, $requesterName, $title, $description !== '' ? $description : null);
            Flash::set('success', 'Chamado #' . ticketReference(['id' => $ticketId, 'protocol_number' => $ticketId]) . ' criado com status Aberto.');
            break;

        case 'start_ticket':
            $ticket = requireTicket($ticketRepository);
            if ($ticket['status'] !== 'Aberto') {
                throw new RuntimeException('Só é possível iniciar um chamado em aberto.');
            }

            $ticketRepository->start((int) $ticket['id']);
            Flash::set('success', 'Check-in registrado com sucesso.');
            break;

        case 'finish_ticket':
            $ticket = requireTicket($ticketRepository);
            if ($ticket['status'] !== 'Em Atendimento' || empty($ticket['started_at'])) {
                throw new RuntimeException('Só é possível finalizar um chamado em atendimento.');
            }

            $responderName = trim((string) ($_POST['responder_name'] ?? ''));
            $whatHappened = trim((string) ($_POST['what_happened'] ?? ''));
            $howSolved = trim((string) ($_POST['how_solved'] ?? ''));
            $delayReason = trim((string) ($_POST['delay_reason'] ?? ''));

            if ($responderName === '') {
                throw new RuntimeException('Informe o nome de quem finalizou o chamado.');
            }

            if ($whatHappened === '' || $howSolved === '') {
                throw new RuntimeException('Descreva o ocorrido e a solução aplicada.');
            }

            if (ticketIsOverdue($ticket) && $delayReason === '') {
                throw new RuntimeException('Informe o motivo do atraso para este chamado.');
            }

            $solution = "O que aconteceu: {$whatHappened}\nComo resolveu: {$howSolved}";

            $ticketRepository->finish((int) $ticket['id'], $responderName, $delayReason !== '' ? $delayReason : null, $solution);
            Flash::set('success', 'Check-out realizado com sucesso.');
            break;

        case 'cancel_ticket':
            $ticket = requireTicket($ticketRepository);
            if (!in_array($ticket['status'], ['Aberto', 'Em Atendimento'], true)) {
                throw new RuntimeException('Só é possível cancelar chamados em aberto ou em atendimento.');
            }

            $canceledBy = trim((string) ($_POST['canceled_by'] ?? ''));
            $cancelReason = trim((string) ($_POST['cancel_reason'] ?? ''));

            if ($canceledBy === '') {
                throw new RuntimeException('Informe o nome de quem cancelou o chamado.');
            }

            if ($cancelReason === '') {
                throw new RuntimeException('Informe o motivo do cancelamento.');
            }

            $ticketRepository->cancel((int) $ticket['id'], $canceledBy, $cancelReason);
            Flash::set('success', 'Cancelamento registrado com sucesso.');
            break;

        default:
            throw new RuntimeException('Ação inválida.');
    }
}

function requireTicket(TicketRepository $ticketRepository): array
{
    $id = requireInt('ticket_id', 'Chamado inválido.');
    $ticket = $ticketRepository->find($id);
    if ($ticket === null) {
        throw new RuntimeException('Chamado não encontrado.');
    }

    return $ticket;
}

function requireInt(string $key, string $message): int
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);
    if ($value === false) {
        throw new RuntimeException($message);
    }

    return (int) $value;
}

function computeTicketDuration(array $ticket): array
{
    $tz = new DateTimeZone('America/Sao_Paulo');
    $referenceStart = !empty($ticket['started_at'])
        ? $ticket['started_at']
        : ($ticket['created_at'] ?? null);

    if ($referenceStart === null || $referenceStart === '') {
        return [0, null];
    }

    $start = new DateTimeImmutable($referenceStart, $tz);
    $endReference = !empty($ticket['ended_at'])
        ? $ticket['ended_at']
        : (!empty($ticket['canceled_at']) ? $ticket['canceled_at'] : null);
    $end = $endReference !== null
        ? new DateTimeImmutable($endReference, $tz)
        : new DateTimeImmutable('now', $tz);
    $minutes = max(0, (int) floor(($end->getTimestamp() - $start->getTimestamp()) / 60));
    $estimatedMinutes = ((int) $ticket['estimated_hours']) * 60;

    return [$minutes, $minutes > $estimatedMinutes ? 'overdue' : null];
}

function summarizeTickets(array $tickets): array
{
    $summary = [
        'total' => count($tickets),
        'aberto' => 0,
        'em_atendimento' => 0,
        'finalizado' => 0,
        'cancelado' => 0,
    ];

    foreach ($tickets as $ticket) {
        switch ($ticket['status']) {
            case 'Aberto':
                $summary['aberto']++;
                break;
            case 'Em Atendimento':
                $summary['em_atendimento']++;
                break;
            case 'Finalizado':
                $summary['finalizado']++;
                break;
            case 'Cancelado':
                $summary['cancelado']++;
                break;
        }
    }

    return $summary;
}

function statusClass(string $status): string
{
    return match ($status) {
        'Aberto' => 'status-open',
        'Em Atendimento' => 'status-progress',
        'Finalizado' => 'status-finished',
        'Cancelado' => 'status-canceled',
        default => '',
    };
}

function priorityClass(string $priorityName): string
{
    return match ($priorityName) {
        'Alta' => 'priority-high',
        'Média' => 'priority-medium',
        'Baixa' => 'priority-low',
        default => '',
    };
}

function pageTitle(string $page): array
{
    return match ($page) {
        'sectors' => ['title' => 'Setores', 'description' => 'Cadastre as áreas responsáveis pelos chamados.'],
        'tickets' => ['title' => 'Abrir Chamado', 'description' => 'Crie novos chamados de forma organizada.'],
        'monitor' => ['title' => 'Acompanhamento', 'description' => 'Visualize o fluxo de abertura, atendimento e encerramento.'],
        default => ['title' => 'Dashboard', 'description' => 'Resumo geral do atendimento e histórico operacional.'],
    };
}

function canStart(array $ticket): bool
{
    return $ticket['status'] === 'Aberto' && empty($ticket['started_at']);
}

function canFinish(array $ticket): bool
{
    return $ticket['status'] === 'Em Atendimento' && !empty($ticket['started_at']) && $ticket['ended_at'] === null;
}

function canCancel(array $ticket): bool
{
    return in_array($ticket['status'], ['Aberto', 'Em Atendimento'], true);
}

function ticketStatusLabel(array $ticket): string
{
    if (ticketIsOverdue($ticket)) {
        return $ticket['status'] === 'Aberto' ? 'Aberto em atraso' : 'Em atraso';
    }

    return match ($ticket['status']) {
        'Aberto' => 'Aberto',
        'Em Atendimento' => 'Em atendimento',
        'Finalizado' => 'Concluído',
        'Cancelado' => 'Cancelado',
        default => 'Atual',
    };
}

function ticketIsOverdue(array $ticket): bool
{
    if (!in_array($ticket['status'], ['Aberto', 'Em Atendimento'], true)) {
        return false;
    }

    [, $flag] = computeTicketDuration($ticket);
    return $flag === 'overdue';
}

function ticketReference(array $ticket): string
{
    $number = $ticket['protocol_number'] ?? $ticket['id'] ?? null;
    if ($number === null) {
        return '000000';
    }

    return str_pad((string) (int) $number, 6, '0', STR_PAD_LEFT);
}

function ticketPriorityRank(array $ticket): int
{
    return match ($ticket['priority_name'] ?? '') {
        'Alta' => 3,
        'Média' => 2,
        'Baixa' => 1,
        default => 0,
    };
}

function ticketStatusRank(array $ticket): int
{
    return match ($ticket['status'] ?? '') {
        'Aberto' => 4,
        'Em Atendimento' => 3,
        'Finalizado' => 2,
        'Cancelado' => 1,
        default => 0,
    };
}

function sortTicketsForMonitor(array $tickets): array
{
    usort($tickets, static function (array $left, array $right): int {
        $leftOverdue = ticketIsOverdue($left) ? 1 : 0;
        $rightOverdue = ticketIsOverdue($right) ? 1 : 0;

        if ($leftOverdue !== $rightOverdue) {
            return $rightOverdue <=> $leftOverdue;
        }

        $priorityComparison = ticketPriorityRank($right) <=> ticketPriorityRank($left);
        if ($priorityComparison !== 0) {
            return $priorityComparison;
        }

        $statusComparison = ticketStatusRank($right) <=> ticketStatusRank($left);
        if ($statusComparison !== 0) {
            return $statusComparison;
        }

        $leftCreated = strtotime((string) ($left['created_at'] ?? '')) ?: 0;
        $rightCreated = strtotime((string) ($right['created_at'] ?? '')) ?: 0;
        if ($leftCreated !== $rightCreated) {
            return $rightCreated <=> $leftCreated;
        }

        return (int) ($right['id'] ?? 0) <=> (int) ($left['id'] ?? 0);
    });

    return $tickets;
}

function filterTicketsByStatus(array $tickets, string $filter): array
{
    return array_values(array_filter($tickets, static function (array $ticket) use ($filter): bool {
        return match ($filter) {
            'open' => $ticket['status'] === 'Aberto',
            'overdue' => ticketIsOverdue($ticket),
            'progress' => $ticket['status'] === 'Em Atendimento',
            'finished' => $ticket['status'] === 'Finalizado',
            'active' => $ticket['status'] !== 'Finalizado',
            default => true,
        };
    }));
}

$meta = pageTitle($page);
$summaryCards = [
    ['label' => 'Total de chamados', 'value' => $summary['total'], 'tone' => 'neutral'],
    ['label' => 'Abertos', 'value' => $summary['aberto'], 'tone' => 'blue'],
    ['label' => 'Em atendimento', 'value' => $summary['em_atendimento'], 'tone' => 'amber'],
    ['label' => 'Finalizados', 'value' => $summary['finalizado'], 'tone' => 'green'],
    ['label' => 'Cancelados', 'value' => $summary['cancelado'], 'tone' => 'rose'],
];
$recentTickets = array_slice($tickets, 0, 6);
$closedTickets = array_values(array_filter($tickets, static fn (array $ticket): bool => in_array($ticket['status'], ['Finalizado', 'Cancelado'], true)));
$recentClosedTickets = array_slice($closedTickets, 0, 4);
$overdueTickets = array_values(array_filter($tickets, static fn (array $ticket): bool => ticketIsOverdue($ticket) && in_array($ticket['status'], ['Aberto', 'Em Atendimento'], true)));
$navItems = [
    ['label' => 'Dashboard', 'page' => 'dashboard', 'description' => 'Resumo e histórico'],
    ['label' => 'Setores', 'page' => 'sectors', 'description' => 'Cadastro de áreas'],
    ['label' => 'Abrir Chamado', 'page' => 'tickets', 'description' => 'Novo atendimento'],
    ['label' => 'Acompanhamento', 'page' => 'monitor', 'description' => 'Check-in e check-out'],
];

require __DIR__ . '/../views/layout.php';
