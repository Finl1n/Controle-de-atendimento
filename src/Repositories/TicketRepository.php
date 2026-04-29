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
    p.estimated_hours AS estimated_hours
FROM tickets t
INNER JOIN sectors s ON s.id = t.sector_id
INNER JOIN priorities p ON p.id = t.priority_id
ORDER BY t.created_at DESC, t.id DESC
SQL;

        return $this->pdo->query($sql)->fetchAll();
    }

    public function create(int $sectorId, int $priorityId, string $title, ?string $description): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            'INSERT INTO tickets (sector_id, priority_id, title, description, status, created_at, updated_at) VALUES (:sector_id, :priority_id, :title, :description, :status, :created_at, :updated_at)'
        );

        $stmt->execute([
            'sector_id' => $sectorId,
            'priority_id' => $priorityId,
            'title' => $title,
            'description' => $description,
            'status' => 'Aberto',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
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

    public function finish(int $id, string $solution): void
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            "UPDATE tickets SET status = 'Finalizado', ended_at = :ended_at, solution = :solution, updated_at = :updated_at WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
            'solution' => $solution,
            'ended_at' => $now,
            'updated_at' => $now,
        ]);
    }

}
