<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class Supplier
{
    public function __construct(private readonly PDO $db)
    {
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT s.id, s.supplier_code, s.company_name, s.contact_person, s.email, s.phone,
                    s.category_specialization_id,
                    c.name AS category_specialization, s.created_at
             FROM suppliers s
             INNER JOIN categories c ON c.id = s.category_specialization_id
             ORDER BY s.id DESC'
        )->fetchAll();
    }

    public function create(array $payload, int $createdBy): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO suppliers (supplier_code, company_name, contact_person, email, phone, category_specialization_id, created_by)
             VALUES (:supplier_code, :company_name, :contact_person, :email, :phone, :category_specialization_id, :created_by)'
        );
        $stmt->execute([
            'supplier_code' => $payload['supplier_code'],
            'company_name' => $payload['company_name'],
            'contact_person' => $payload['contact_person'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'category_specialization_id' => $payload['category_specialization_id'],
            'created_by' => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $payload): void
    {
        $stmt = $this->db->prepare(
            'UPDATE suppliers
             SET company_name = :company_name,
                 contact_person = :contact_person,
                 email = :email,
                 phone = :phone,
                 category_specialization_id = :category_specialization_id
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'company_name' => $payload['company_name'],
            'contact_person' => $payload['contact_person'],
            'email' => $payload['email'],
            'phone' => $payload['phone'],
            'category_specialization_id' => $payload['category_specialization_id'],
        ]);
    }
}
