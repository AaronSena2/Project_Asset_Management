<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use PDO;

final class Asset
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function all(): array
    {
        $rows = $this->db->query(
            'SELECT a.id, a.serial_number, c.name AS category_name, s.name AS status_name,
                    a.date_of_purchase, sup.company_name AS supplier_name,
                    u.full_name AS assigned_to
             FROM assets a
             INNER JOIN categories c ON c.id = a.category_id
             INNER JOIN asset_statuses s ON s.id = a.status_id
             INNER JOIN suppliers sup ON sup.id = a.supplier_id
             LEFT JOIN users u ON u.id = a.assigned_to_user_id
             ORDER BY a.id DESC'
        )->fetchAll();

        foreach ($rows as &$row) {
            $row['age'] = $this->calculateAssetAge($row['date_of_purchase']);
        }

        return $rows;
    }

    public function statuses(): array
    {
        return $this->db->query('SELECT id, name FROM asset_statuses ORDER BY id ASC')->fetchAll();
    }

    public function create(array $payload, int $createdBy): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO assets (serial_number, category_id, status_id, date_of_purchase, supplier_id, assigned_to_user_id, created_by)
             VALUES (:serial_number, :category_id, :status_id, :date_of_purchase, :supplier_id, :assigned_to_user_id, :created_by)'
        );
        $stmt->execute([
            'serial_number' => $payload['serial_number'],
            'category_id' => $payload['category_id'],
            'status_id' => $payload['status_id'],
            'date_of_purchase' => $payload['date_of_purchase'],
            'supplier_id' => $payload['supplier_id'],
            'assigned_to_user_id' => $payload['assigned_to_user_id'] ?: null,
            'created_by' => $createdBy,
        ]);

        $assetId = (int) $this->db->lastInsertId();
        if (!empty($payload['specifications']) && is_array($payload['specifications'])) {
            $specInsert = $this->db->prepare(
                'INSERT INTO asset_specifications (asset_id, specification_definition_id, value_text)
                 VALUES (:asset_id, :specification_definition_id, :value_text)'
            );

            foreach ($payload['specifications'] as $specDefinitionId => $value) {
                if ((string) $value === '') {
                    continue;
                }

                $specInsert->execute([
                    'asset_id' => $assetId,
                    'specification_definition_id' => (int) $specDefinitionId,
                    'value_text' => (string) $value,
                ]);
            }
        }

        return $assetId;
    }

    public function calculateAssetAge(string $dateOfPurchase): string
    {
        $purchaseDate = new DateTimeImmutable($dateOfPurchase);
        $now = new DateTimeImmutable('now');
        $difference = $purchaseDate->diff($now);

        return sprintf('%d years, %d months', $difference->y, $difference->m);
    }

    public function countByCategory(): array
    {
        return $this->db->query(
            'SELECT c.name AS label, COUNT(a.id) AS total
             FROM categories c
             LEFT JOIN assets a ON a.category_id = c.id
             GROUP BY c.id, c.name
             ORDER BY c.name ASC'
        )->fetchAll();
    }

    public function countByStatus(): array
    {
        return $this->db->query(
            'SELECT s.name AS label, COUNT(a.id) AS total
             FROM asset_statuses s
             LEFT JOIN assets a ON a.status_id = s.id
             GROUP BY s.id, s.name
             ORDER BY s.id ASC'
        )->fetchAll();
    }

    public function totalCount(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM assets')->fetchColumn();
    }
}
