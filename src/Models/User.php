<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;

final class User
{
    public const ROLE_SYSTEM_ADMINISTRATOR = 'System Administrator';
    public const ROLE_OFFICE_ADMINISTRATOR = 'Office Administrator';
    public const ROLE_IT_MANAGER = 'IT Manager';
    public const ROLE_FINANCE_MANAGER = 'Finance Manager';
    private ?string $userIdColumn = null;
    /** @var array<string, true>|null */
    private ?array $usersColumns = null;

    public function __construct(private readonly PDO $db)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $userIdColumn = $this->userIdColumn();
        $stmt = $this->db->prepare(
            sprintf(
                'SELECT u.`%1$s` AS id, u.full_name, u.email, u.password_hash, r.name AS role_name
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             WHERE u.email = :email AND u.is_active = 1',
                $userIdColumn
            )
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function officeAdministratorEmail(): ?string
    {
        $userIdColumn = $this->userIdColumn();
        $stmt = $this->db->query(
            sprintf(
                "SELECT u.email FROM users u INNER JOIN roles r ON r.id = u.role_id WHERE r.name = 'Office Administrator' ORDER BY u.`%s` ASC LIMIT 1",
                $userIdColumn
            )
        );

        return $stmt->fetchColumn() ?: null;
    }

    public function all(): array
    {
        $userIdColumn = $this->userIdColumn();
        return $this->db->query(
            sprintf(
                'SELECT u.`%1$s` AS id, u.full_name, u.email, u.role_id, r.name AS role_name, u.is_active, u.created_at
             FROM users u
             INNER JOIN roles r ON r.id = u.role_id
             ORDER BY u.`%1$s` DESC',
                $userIdColumn
            )
        )->fetchAll();
    }

    public function countAll(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn();
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
        $stmt = $this->db->prepare(
            sprintf('UPDATE users SET role_id = :role_id WHERE `%s` = :id', $this->userIdColumn())
        );
        $stmt->execute([
            'role_id' => $roleId,
            'id' => $userId,
        ]);
    }

    private function userIdColumn(): string
    {
        if ($this->userIdColumn !== null) {
            return $this->userIdColumn;
        }

        $primaryKeyStmt = $this->db->prepare("SHOW KEYS FROM users WHERE Key_name = 'PRIMARY'");
        $primaryKeyStmt->execute();
        $primaryKey = $primaryKeyStmt->fetch();
        if (is_array($primaryKey) && isset($primaryKey['Column_name'])) {
            $this->userIdColumn = $this->assertSafeIdentifier((string) $primaryKey['Column_name']);

            return $this->userIdColumn;
        }

        foreach (['id', 'user_id'] as $fallbackColumn) {
            $stmt = $this->db->prepare('SHOW COLUMNS FROM users LIKE :column');
            $stmt->execute(['column' => $fallbackColumn]);
            if ($stmt->fetch() !== false) {
                $this->userIdColumn = $this->assertSafeIdentifier($fallbackColumn);

                return $this->userIdColumn;
            }
        }

        throw new RuntimeException('Unable to determine users table identifier column.');
    }

    private function assertSafeIdentifier(string $column): string
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $column) !== 1 || !isset($this->usersColumns()[$column])) {
            throw new RuntimeException('Invalid users table identifier column.');
        }

        return $column;
    }

    /** @return array<string, true> */
    private function usersColumns(): array
    {
        if ($this->usersColumns !== null) {
            return $this->usersColumns;
        }

        $columnsStmt = $this->db->prepare('SHOW COLUMNS FROM users');
        $columnsStmt->execute();
        $columns = [];

        foreach ($columnsStmt->fetchAll() as $columnDefinition) {
            if (isset($columnDefinition['Field'])) {
                $columns[(string) $columnDefinition['Field']] = true;
            }
        }

        $this->usersColumns = $columns;

        return $this->usersColumns;
    }
}
