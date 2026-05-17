<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

final class User
{
    public const ROLE_SYSTEM_ADMINISTRATOR = 'System Administrator';
    public const ROLE_OFFICE_ADMINISTRATOR = 'Office Administrator';
    public const ROLE_IT_MANAGER = 'IT Manager';
    public const ROLE_FINANCE_MANAGER = 'Finance Manager';

    public function __construct(private readonly PDO $db)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT u.id, u.full_name, u.email, u.password_hash, r.name AS role_name
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.is_active = 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function officeAdministratorEmail(): ?string
    {
        $stmt = $this->db->query(
            "SELECT u.email FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.name = 'Office Administrator' ORDER BY u.id ASC LIMIT 1"
        );

        return $stmt->fetchColumn() ?: null;
    }

    public function all(): array
    {
        return $this->db->query(
            'SELECT u.id, u.full_name, u.email, r.name AS role_name, u.is_active, u.created_at
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             ORDER BY u.id DESC'
        )->fetchAll();
    }

    public function roles(): array
    {
        return $this->db->query('SELECT id, name FROM roles ORDER BY id ASC')->fetchAll();
    }

    public function create(array $payload, int $createdBy): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (full_name, email, password_hash, role_id, created_by)
             VALUES (:full_name, :email, :password_hash, :role_id, :created_by)'
        );
        $stmt->execute([
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'password_hash' => password_hash($payload['password'], PASSWORD_DEFAULT),
            'role_id' => $payload['role_id'],
            'created_by' => $createdBy,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function updateRole(int $userId, int $roleId): void
    {
        $stmt = $this->db->prepare('UPDATE users SET role_id = :role_id WHERE id = :id');
        $stmt->execute([
            'role_id' => $roleId,
            'id' => $userId,
        ]);
    }
}
