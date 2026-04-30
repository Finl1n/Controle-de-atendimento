<?php

declare(strict_types=1);

final class PriorityRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findOrCreate(string $name, int $defaultEstimatedHours = 1): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM priorities WHERE name = :name');
        $stmt->execute(['name' => $name]);
        $existing = $stmt->fetchColumn();

        if ($existing !== false) {
            return (int) $existing;
        }

        $stmt = $this->pdo->prepare('INSERT INTO priorities (name, estimated_hours) VALUES (:name, :estimated_hours)');
        $stmt->execute([
            'name' => $name,
            'estimated_hours' => $defaultEstimatedHours,
        ]);

        return (int) $this->pdo->lastInsertId();
    }
}
