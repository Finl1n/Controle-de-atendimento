<?php

declare(strict_types=1);

final class PriorityRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function upsert(string $name, int $estimatedHours): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM priorities WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $existing = $stmt->fetchColumn();

        if ($existing !== false) {
            $stmt = $this->pdo->prepare('UPDATE priorities SET estimated_hours = :estimated_hours WHERE id = :id');
            $stmt->execute([
                'id' => (int) $existing,
                'estimated_hours' => $estimatedHours,
            ]);

            return (int) $existing;
        }

        $stmt = $this->pdo->prepare('INSERT INTO priorities (name, estimated_hours) VALUES (:name, :estimated_hours)');
        $stmt->execute([
            'name' => $name,
            'estimated_hours' => $estimatedHours,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
