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

$page = $_GET['page'] ?? 'dashboard';
if (!in_array($page, ['dashboard', 'sectors', 'priorities', 'tickets', 'monitor'], true)) {
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
$priorities = $priorityRepository->all();
$tickets = $ticketRepository->allWithRelations();
$summary = summarizeTickets($tickets);

function ensureSchema(PDO $pdo): void
{
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name IN ('sectors', 'priorities', 'tickets')")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) === 3) {
        return;
    }

    $pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
    $pdo->exec(file_get_contents(__DIR__ . '/../database/seed.sql'));
}

function handleAction(?string $action, SectorRepository $sectorRepository, PriorityRepository $priorityRepository, TicketRepository $ticketRepository): void
{
    if ($action === null || $_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    switch ($action) {
        case 'create_sector':
            $name = trim((string)($_POST['name'] ?? ''));
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

        case 'create_priority':
            $name = trim((string)($_POST['name'] ?? ''));
            $estimatedHours = filter_var($_POST['estimated_hours'] ?? null, FILTER_VALIDATE_INT);

            if (!in_array($name, ['Baixa', 'Média', 'Alta'], true)) {
                throw new RuntimeException('Selecione uma prioridade válida.');
            }
            if ($priorityRepository->existsByName($name)) {
                throw new RuntimeException('Essa prioridade já existe.');
            }

            if ($estimatedHours === false || $estimatedHours <= 0) {
                throw new RuntimeException('Informe um tempo estimado válido em horas.');
            }

            $priorityRepository->create($name, $estimatedHours);
            Flash::set('success', 'Prioridade cadastrada com sucesso.');
            break;

        case 'delete_priority':
            $priorityId = requireInt('priority_id', 'Prioridade inválida.');
            if ($priorityRepository->countTickets($priorityId) > 0) {
                throw new RuntimeException('Não é possível excluir uma prioridade que já possui chamados.');
            }

            $priorityRepository->delete($priorityId);
            Flash::set('success', 'Prioridade removida com sucesso.');
            break;

        case 'create_ticket':
            $sectorId = filter_var($_POST['sector_id'] ?? null, FILTER_VALIDATE_INT);
            $priorityId = filter_var($_POST['priority_id'] ?? null, FILTER_VALIDATE_INT);
            $title = trim((string)($_POST['title'] ?? ''));
            $description = trim((string)($_POST['description'] ?? ''));

            if ($sectorId === false || $priorityId === false) {
                throw new RuntimeException('Selecione setor e prioridade.');
            }
            if (!$sectorRepository->exists($sectorId)) {
                throw new RuntimeException('O setor selecionado não existe.');
            }
            if (!$priorityRepository->exists($priorityId)) {
                throw new RuntimeException('A prioridade selecionada não existe.');
            }

            if ($title === '') {
                throw new RuntimeException('Informe o título do chamado.');
            }

            $ticketRepository->create($sectorId, $priorityId, $title, $description !== '' ? $description : null);
            Flash::set('success', 'Chamado criado com status Aberto.');
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

            $whatHappened = trim((string)($_POST['what_happened'] ?? ''));
            $howSolved = trim((string)($_POST['how_solved'] ?? ''));
            if ($whatHappened === '' || $howSolved === '') {
                throw new RuntimeException('Descreva o ocorrido e a solução aplicada.');
            }

            $solution = "O que aconteceu: {$whatHappened}\nComo resolveu: {$howSolved}";
            $ticketRepository->finish((int) $ticket['id'], $solution);
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
    if (empty($ticket['started_at'])) {
        return [0, null];
    }

    $tz = new DateTimeZone('America/Sao_Paulo');
    $start = new DateTimeImmutable($ticket['started_at'], $tz);
    $end = !empty($ticket['ended_at']) ? new DateTimeImmutable($ticket['ended_at'], $tz) : new DateTimeImmutable('now', $tz);
    $minutes = max(0, (int) floor(($end->getTimestamp() - $start->getTimestamp()) / 60));
    $estimatedMinutes = ((int)$ticket['estimated_hours']) * 60;

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

function pageTitle(string $page): array
{
    return match ($page) {
        'sectors' => ['title' => 'Setores', 'description' => 'Cadastre as áreas responsáveis pelos chamados.'],
        'priorities' => ['title' => 'Prioridades', 'description' => 'Defina o SLA e a urgência de atendimento.'],
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

function ticketStatusLabel(array $ticket): string
{
    if (ticketIsOverdue($ticket)) {
        return 'Em atraso';
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

$meta = pageTitle($page);
$summaryCards = [
    ['label' => 'Total de chamados', 'value' => $summary['total'], 'tone' => 'neutral'],
    ['label' => 'Abertos', 'value' => $summary['aberto'], 'tone' => 'blue'],
    ['label' => 'Em atendimento', 'value' => $summary['em_atendimento'], 'tone' => 'amber'],
    ['label' => 'Finalizados', 'value' => $summary['finalizado'], 'tone' => 'green'],
];
$recentTickets = array_slice($tickets, 0, 6);
$overdueTickets = array_values(array_filter($tickets, static fn(array $ticket): bool => ticketIsOverdue($ticket)));
$navItems = [
    ['label' => 'Dashboard', 'page' => 'dashboard', 'description' => 'Resumo e histórico'],
    ['label' => 'Setores', 'page' => 'sectors', 'description' => 'Cadastro de áreas'],
    ['label' => 'Prioridades', 'page' => 'priorities', 'description' => 'SLA e urgência'],
    ['label' => 'Abrir Chamado', 'page' => 'tickets', 'description' => 'Novo atendimento'],
    ['label' => 'Acompanhamento', 'page' => 'monitor', 'description' => 'Check-in e check-out'],
];

require __DIR__ . '/../views/layout.php';
