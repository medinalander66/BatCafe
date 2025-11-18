<?php
// src/EquipmentRepository.php
declare(strict_types=1);

class EquipmentRepository
{
    private PDO $pdo;
    public function __construct(PDO $pdo) { $this->pdo = $pdo; }

    public function all(): array
    {
        $stmt = $this->pdo->query("SELECT id, code, name, rate_per_hour, created_at FROM equipment ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, code, name, rate_per_hour FROM equipment WHERE code = :code LIMIT 1");
        $stmt->execute(['code' => $code]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
