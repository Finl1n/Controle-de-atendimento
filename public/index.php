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

$currentRole = $_SESSION['app_role'] ?? null;
$currentUserName = trim((string) ($_SESSION['user_name'] ?? ''));

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
$allTickets = $ticketRepository->allWithRelations();
$tickets = sortTicketsForMonitor(filterTicketsForRole($allTickets, $currentRole, $currentUserName));
$summary = summarizeTickets($tickets);

if ($currentRole === null) {
    $page = 'role';
} else {
    $allowedPages = $currentRole === 'solicitante'
        ? ['dashboard', 'sectors', 'tickets']
        : ['dashboard', 'monitor'];

    if (!in_array($page, $allowedPages, true)) {
        $page = $allowedPages[0];
    }
}

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
    ];

    foreach ($required as $column => $sql) {
        if (!in_array($column, $existing, true)) {
            $pdo->exec($sql);
        }
    }

    $pdo->exec('UPDATE tickets SET protocol_number = id WHERE protocol_number IS NULL');
    $pdo->exec("UPDATE tickets SET requester_name = 'Solicitante não informado' WHERE requester_name IS NULL OR requester_name = ''");
}

function handleAction(?string $action, SectorRepository $sectorRepository, PriorityRepository $priorityRepository, TicketRepository $ticketRepository): void
{
    if ($action === null || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    switch ($action) {
        case 'select_role':
            $role = trim((string) ($_POST['role'] ?? ''));
            $name = trim((string) ($_POST['name'] ?? ''));

            if (!in_array($role, ['solicitante', 'responsavel'], true)) {
                throw new RuntimeException('Selecione um perfil válido.');
            }

            if ($name === '') {
                throw new RuntimeException('Informe seu nome para continuar.');
            }

            $_SESSION['app_role'] = $role;
            $_SESSION['user_name'] = $name;
            header('Location: ?page=dashboard');
            exit;

        case 'reset_role':
            unset($_SESSION['app_role'], $_SESSION['user_name']);
            header('Location: ?page=role');
            exit;

        case 'create_sector':
            requireRole('solicitante');
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
            requireRole('solicitante');
            $sectorId = requireInt('sector_id', 'Setor inválido.');
            if ($sectorRepository->countTickets($sectorId) > 0) {
                throw new RuntimeException('Não é possível excluir um setor que já possui chamados.');
            }

            $sectorRepository->delete($sectorId);
            Flash::set('success', 'Setor removido com sucesso.');
            break;

        case 'create_ticket':
            requireRole('solicitante');
            $sectorId = filter_var($_POST['sector_id'] ?? null, FILTER_VALIDATE_INT);
            $priorityName = trim((string) ($_POST['priority_name'] ?? ''));
            $estimatedHours = filter_var($_POST['estimated_hours'] ?? null, FILTER_VALIDATE_INT);
            $requesterName = trim((string) ($_SESSION['user_name'] ?? ''));
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
            $ticketId = $ticketRepository->create((int) $sectorId, $priorityId, $requesterName, $title, $description !== '' ? $description : null);
            Flash::set('success', 'Chamado #' . ticketReference(['id' => $ticketId, 'protocol_number' => $ticketId]) . ' criado com status Aberto.');
            break;

        case 'start_ticket':
            requireRole('responsavel');
            $ticket = requireTicket($ticketRepository);
            if ($ticket['status'] !== 'Aberto') {
                throw new RuntimeException('Só é possível iniciar um chamado em aberto.');
            }

            $ticketRepository->start((int) $ticket['id']);
            Flash::set('success', 'Check-in registrado com sucesso.');
            break;

        case 'finish_ticket':
            requireRole('responsavel');
            $ticket = requireTicket($ticketRepository);
            if ($ticket['status'] !== 'Em Atendimento' || empty($ticket['started_at'])) {
                throw new RuntimeException('Só é possível finalizar um chamado em atendimento.');
            }

            $responderName = trim((string) ($_POST['responder_name'] ?? ($_SESSION['user_name'] ?? '')));
            $whatHappened = trim((string) ($_POST['what_happened'] ?? ''));
            $howSolved = trim((string) ($_POST['how_solved'] ?? ''));
            $delayReason = trim((string) ($_POST['delay_reason'] ?? ''));

            if ($responderName === '') {
                throw new RuntimeException('Informe o nome de quem finalizou o chamado.');
            }

            if ($whatHappened === '' || $howSolved === '') {
                throw new RuntimeException('Descreva o ocorrido e a solução aplicada.');
            }

            $isOverdue = ticketIsOverdue($ticket);

            if ($isOverdue && $delayReason === '') {
                throw new RuntimeException('Informe o motivo do atraso para este chamado.');
            }

            $solution = "O que aconteceu: {$whatHappened}\nComo resolveu: {$howSolved}";
            $storedDelayReason = $isOverdue && $delayReason !== '' ? $delayReason : null;

            if ($storedDelayReason !== null) {
                $solution = "Motivo do atraso: {$delayReason}\n\n" . $solution;
            }

            $ticketRepository->finish((int) $ticket['id'], $responderName, $storedDelayReason, $solution);
            Flash::set('success', 'Check-out realizado com sucesso.');
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

function requireRole(string $requiredRole): void
{
    $currentRole = $_SESSION['app_role'] ?? null;
    if ($currentRole !== $requiredRole) {
        throw new RuntimeException('Seu perfil atual não permite esta ação.');
    }
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
    $end = !empty($ticket['ended_at'])
        ? new DateTimeImmutable($ticket['ended_at'], $tz)
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

function roleLabel(?string $role): string
{
    return match ($role) {
        'solicitante' => 'Solicitante',
        'responsavel' => 'Responsável',
        default => 'Perfil não selecionado',
    };
}

function pageTitle(string $page, ?string $role = null): array
{
    return match ($page) {
        'role' => ['title' => 'Escolha seu perfil', 'description' => 'Defina como deseja usar o sistema.'],
        'sectors' => ['title' => 'Setores', 'description' => 'Cadastre as áreas responsáveis pelos chamados.'],
        'tickets' => ['title' => 'Abrir Chamado', 'description' => 'Crie novos chamados de forma organizada.'],
        'monitor' => ['title' => 'Acompanhamento', 'description' => 'Visualize o fluxo de abertura, atendimento e encerramento.'],
        default => $role === 'solicitante'
            ? ['title' => 'Meus chamados', 'description' => 'Acompanhe os chamados solicitados por você.']
            : ['title' => 'Dashboard', 'description' => 'Resumo geral do atendimento e histórico operacional.'],
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

function ticketStatusLabel(array $ticket): string
{
    if (ticketIsOverdue($ticket)) {
        return $ticket['status'] === 'Aberto' ? 'Pendente em atraso' : 'Em atraso';
    }

    return match ($ticket['status']) {
        'Aberto' => 'Aguardando início',
        'Em Atendimento' => 'Em atendimento',
        'Finalizado' => 'Concluído',
        default => 'Atual',
    };
}

function ticketIsOverdue(array $ticket): bool
{
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

function filterTicketsForRole(array $tickets, ?string $role, string $userName): array
{
    if ($role !== 'solicitante' || $userName === '') {
        return $tickets;
    }

    return array_values(array_filter($tickets, static function (array $ticket) use ($userName): bool {
        return trim((string) ($ticket['requester_name'] ?? '')) === $userName;
    }));
}

function ticketPriorityRank(string $priorityName): int
{
    return match ($priorityName) {
        'Alta' => 0,
        'Média' => 1,
        'Baixa' => 2,
        default => 3,
    };
}

function ticketStatusRank(string $status): int
{
    return match ($status) {
        'Aberto' => 0,
        'Em Atendimento' => 1,
        'Finalizado' => 2,
        default => 3,
    };
}

function sortTicketsForMonitor(array $tickets): array
{
    usort($tickets, static function (array $left, array $right): int {
        $leftStatus = ticketStatusRank((string) ($left['status'] ?? ''));
        $rightStatus = ticketStatusRank((string) ($right['status'] ?? ''));

        if ($leftStatus !== $rightStatus) {
            return $leftStatus <=> $rightStatus;
        }

        $leftPriority = ticketPriorityRank((string) ($left['priority_name'] ?? ''));
        $rightPriority = ticketPriorityRank((string) ($right['priority_name'] ?? ''));

        if ($leftPriority !== $rightPriority) {
            return $leftPriority <=> $rightPriority;
        }

        return strcmp((string) ($right['created_at'] ?? ''), (string) ($left['created_at'] ?? ''));
    });

    return $tickets;
}

function filterTicketsByStatus(array $tickets, string $statusFilter): array
{
    if ($statusFilter === 'all') {
        return $tickets;
    }

    $status = match ($statusFilter) {
        'open' => 'Aberto',
        'progress' => 'Em Atendimento',
        'finished' => 'Finalizado',
        default => null,
    };

    if ($status === null) {
        return $tickets;
    }

    return array_values(array_filter($tickets, static fn (array $ticket): bool => ($ticket['status'] ?? '') === $status));
}

$meta = pageTitle($page, $currentRole);
$summaryCards = [
    ['label' => 'Total de chamados', 'value' => $summary['total'], 'tone' => 'neutral'],
    ['label' => 'Abertos', 'value' => $summary['aberto'], 'tone' => 'blue'],
    ['label' => 'Em atendimento', 'value' => $summary['em_atendimento'], 'tone' => 'amber'],
    ['label' => 'Finalizados', 'value' => $summary['finalizado'], 'tone' => 'green'],
];
$recentTickets = array_slice($tickets, 0, 6);
$closedTickets = array_values(array_filter($tickets, static fn (array $ticket): bool => $ticket['status'] === 'Finalizado'));
$recentClosedTickets = array_slice($closedTickets, 0, 4);
$overdueTickets = array_values(array_filter($tickets, static fn (array $ticket): bool => ticketIsOverdue($ticket)));
$monitorStatusFilter = $_GET['status'] ?? 'all';
if (!in_array($monitorStatusFilter, ['all', 'open', 'progress', 'finished'], true)) {
    $monitorStatusFilter = 'all';
}
$monitorTickets = sortTicketsForMonitor(filterTicketsByStatus($tickets, $monitorStatusFilter));
$navItems = $currentRole === 'solicitante'
    ? [
        ['label' => 'Dashboard', 'page' => 'dashboard', 'description' => 'Seus chamados'],
        ['label' => 'Setores', 'page' => 'sectors', 'description' => 'Cadastro de áreas'],
        ['label' => 'Abrir Chamado', 'page' => 'tickets', 'description' => 'Novo atendimento'],
    ]
    : [
        ['label' => 'Dashboard', 'page' => 'dashboard', 'description' => 'Resumo da operação'],
        ['label' => 'Acompanhamento', 'page' => 'monitor', 'description' => 'Check-in e check-out'],
    ];

require __DIR__ . '/../views/layout.php';
