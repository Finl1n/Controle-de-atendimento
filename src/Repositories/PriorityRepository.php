<?php

declare(strict_types=1);

final class PriorityRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(): array
    {
        return $this->pdo->query('SELECT id, name, estimated_hours FROM priorities ORDER BY estimated_hours DESC, name')->fetchAll();
    }

    public function create(string $name, int $estimatedHours): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO priorities (name, estimated_hours) VALUES (:name, :estimated_hours)');
        $stmt->execute([
            'name' => $name,
            'estimated_hours' => $estimatedHours,
        ]);
    }

    public function existsByName(string $name): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM priorities WHERE name = :name');
        $stmt->execute(['name' => $name]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM priorities WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function countTickets(int $id): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM tickets WHERE priority_id = :id');
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn();
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM priorities WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
