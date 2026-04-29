<?php

declare(strict_types=1);

final class SectorRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all(): array
    {
        return $this->pdo->query('SELECT id, name FROM sectors ORDER BY name')->fetchAll();
    }

    public function create(string $name): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO sectors (name) VALUES (:name)');
        $stmt->execute(['name' => $name]);
    }

    public function existsByName(string $name): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM sectors WHERE name = :name');
        $stmt->execute(['name' => $name]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function exists(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM sectors WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function countTickets(int $id): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM tickets WHERE sector_id = :id');
        $stmt->execute(['id' => $id]);

        return (int) $stmt->fetchColumn();
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM sectors WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
