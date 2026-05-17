<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Category
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function all(): array
    {
        return $this->db->query('SELECT id, name FROM categories ORDER BY name ASC')->fetchAll();
    }

    public function specificationDefinitionsByCategoryId(int $categoryId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, category_id, field_key, field_label, field_type, is_required
             FROM specification_definitions
             WHERE category_id = :category_id
             ORDER BY id ASC'
        );
        $stmt->execute(['category_id' => $categoryId]);

        return $stmt->fetchAll();
    }

    public function allSpecificationDefinitions(): array
    {
        return $this->db->query(
            'SELECT sd.id, c.name AS category_name, sd.field_key, sd.field_label, sd.field_type, sd.is_required
             FROM specification_definitions sd
             INNER JOIN categories c ON c.id = sd.category_id
             ORDER BY c.name ASC, sd.id ASC'
        )->fetchAll();
    }

    public function createSpecificationDefinition(array $payload, int $createdBy): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO specification_definitions (category_id, field_key, field_label, field_type, is_required, created_by)
             VALUES (:category_id, :field_key, :field_label, :field_type, :is_required, :created_by)'
        );
        $stmt->execute([
            'category_id' => $payload['category_id'],
            'field_key' => $payload['field_key'],
            'field_label' => $payload['field_label'],
            'field_type' => $payload['field_type'],
            'is_required' => !empty($payload['is_required']) ? 1 : 0,
            'created_by' => $createdBy,
        ]);
    }
}
