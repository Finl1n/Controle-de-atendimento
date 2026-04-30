<?php

declare(strict_types=1);

final class TicketRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function allWithRelations(): array
    {
        $sql = <<<SQL
SELECT
    t.*,
    s.name AS sector_name,
    p.name AS priority_name,
    COALESCE(t.estimated_hours, p.estimated_hours) AS estimated_hours
FROM tickets t
INNER JOIN sectors s ON s.id = t.sector_id
INNER JOIN priorities p ON p.id = t.priority_id
ORDER BY t.created_at DESC, t.id DESC
SQL;

        return $this->pdo->query($sql)->fetchAll();
    }

    public function create(int $sectorId, int $priorityId, int $estimatedHours, string $requesterName, string $title, ?string $description): int
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            'INSERT INTO tickets (sector_id, priority_id, estimated_hours, requester_name, title, description, status, created_at, updated_at) VALUES (:sector_id, :priority_id, :estimated_hours, :requester_name, :title, :description, :status, :created_at, :updated_at)'
        );

        $stmt->execute([
            'sector_id' => $sectorId,
            'priority_id' => $priorityId,
            'estimated_hours' => $estimatedHours,
            'requester_name' => $requesterName,
            'title' => $title,
            'description' => $description,
            'status' => 'Aberto',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $protocolNumber = $id;
        $update = $this->pdo->prepare('UPDATE tickets SET protocol_number = :protocol_number WHERE id = :id');
        $update->execute([
            'protocol_number' => $protocolNumber,
            'id' => $id,
        ]);

        return $id;
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM tickets WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $ticket = $stmt->fetch();
        return $ticket === false ? null : $ticket;
    }

    public function start(int $id): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            "UPDATE tickets SET status = 'Em Atendimento', started_at = :started_at, updated_at = :updated_at WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
            'started_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function finish(int $id, string $responderName, ?string $delayReason, string $solution): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            "UPDATE tickets SET status = 'Finalizado', ended_at = :ended_at, responder_name = :responder_name, delay_reason = :delay_reason, solution = :solution, updated_at = :updated_at WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
            'responder_name' => $responderName,
            'delay_reason' => $delayReason,
            'solution' => $solution,
            'ended_at' => $now,
            'updated_at' => $now,
        ]);
    }

    public function cancel(int $id, string $canceledBy, string $cancelReason): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            "UPDATE tickets SET status = 'Cancelado', canceled_at = :canceled_at, canceled_by = :canceled_by, cancel_reason = :cancel_reason, updated_at = :updated_at WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
            'canceled_by' => $canceledBy,
            'cancel_reason' => $cancelReason,
            'canceled_at' => $now,
            'updated_at' => $now,
        ]);
    }

}
