<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use PDO;
use RuntimeException;

final class BootstrapService
{
    public function __construct(
        private readonly PDO $db,
        private readonly string $defaultAdminEmail,
        private readonly string $defaultAdminPassword,
        private readonly string $defaultAdminName = 'System Administrator'
    )
    {
    }

    public function ensureDefaults(): void
    {
        $this->ensureRoles();
        $this->ensureDefaultSystemAdministrator();
    }

    private function ensureRoles(): void
    {
        $insertRole = $this->db->prepare('INSERT IGNORE INTO roles (name) VALUES (:name)');

        foreach ([
            User::ROLE_SYSTEM_ADMINISTRATOR,
            User::ROLE_OFFICE_ADMINISTRATOR,
            User::ROLE_IT_MANAGER,
            User::ROLE_FINANCE_MANAGER,
        ] as $roleName) {
            $insertRole->execute(['name' => $roleName]);
        }
    }

    private function ensureDefaultSystemAdministrator(): void
    {
        $roleId = $this->systemAdministratorRoleId();
        $countStmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE role_id = :role_id');
        $countStmt->execute(['role_id' => $roleId]);

        if ((int) $countStmt->fetchColumn() > 0) {
            return;
        }

        $columns = ['full_name', 'email', 'password_hash', 'role_id'];
        $values = [
            'full_name' => $this->defaultAdminName,
            'email' => $this->defaultAdminEmail,
            'password_hash' => password_hash($this->defaultAdminPassword, PASSWORD_DEFAULT),
            'role_id' => $roleId,
        ];

        $createdByStmt = $this->db->prepare("SHOW COLUMNS FROM users LIKE 'created_by'");
        $createdByStmt->execute();
        $createdByExists = $createdByStmt->fetch() !== false;
        if ($createdByExists) {
            $columns[] = 'created_by';
            $values['created_by'] = null;
        }

        $columnSql = implode(', ', $columns);
        $placeholderSql = implode(', ', array_map(static fn (string $column): string => ':' . $column, $columns));

        $insertUser = $this->db->prepare(sprintf('INSERT INTO users (%s) VALUES (%s)', $columnSql, $placeholderSql));
        $insertUser->execute($values);
    }

    private function systemAdministratorRoleId(): int
    {
        $stmt = $this->db->prepare('SELECT id FROM roles WHERE name = :name LIMIT 1');
        $stmt->execute(['name' => User::ROLE_SYSTEM_ADMINISTRATOR]);
        $roleId = $stmt->fetchColumn();

        if ($roleId === false) {
            throw new RuntimeException('System Administrator role not found.');
        }

        return (int) $roleId;
    }
}
