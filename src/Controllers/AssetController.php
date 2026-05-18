<?php

declare(strict_types=1);

namespace App\Controllers;

use DateTimeImmutable;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use App\Services\AuthService;
use App\Services\NotificationService;
use App\Support\View;
use RuntimeException;
use Throwable;

final class AssetController
{
    /** @var array<int, string>|null */
    private ?array $categoryNamesById = null;

    public function __construct(
        private readonly AuthService $auth,
        private readonly Asset $assets,
        private readonly Supplier $suppliers,
        private readonly Category $categories,
        private readonly User $users,
        private readonly NotificationService $notifications,
        private readonly string $fallbackOfficeEmail
    ) {
    }

    public function index(): void
    {
        $this->auth->requireRole([
            User::ROLE_SYSTEM_ADMINISTRATOR,
            User::ROLE_OFFICE_ADMINISTRATOR,
            User::ROLE_IT_MANAGER,
            User::ROLE_FINANCE_MANAGER,
        ]);

        $user = $this->auth->user();
        $categories = $this->categories->all();
        $manageableCategoryIds = [];
        foreach ($categories as $category) {
            if ($this->canManageCategory($user['role_name'], (int) $category['id'])) {
                $manageableCategoryIds[] = (int) $category['id'];
            }
        }

        View::render('assets/index', [
            'assets' => $this->assets->all(),
            'suppliers' => $this->suppliers->all(),
            'categories' => $categories,
            'statuses' => $this->assets->statuses(),
            'users' => $this->users->all(),
            'manageableCategoryIds' => $manageableCategoryIds,
            'canManageFurniture' => $this->auth->hasRole([
                User::ROLE_SYSTEM_ADMINISTRATOR,
                User::ROLE_OFFICE_ADMINISTRATOR,
            ]),
            'canManageTechnical' => $this->auth->hasRole([
                User::ROLE_SYSTEM_ADMINISTRATOR,
                User::ROLE_IT_MANAGER,
            ]),
        ]);
    }

    public function create(array $post): void
    {
        $this->auth->requireRole([
            User::ROLE_SYSTEM_ADMINISTRATOR,
            User::ROLE_OFFICE_ADMINISTRATOR,
            User::ROLE_IT_MANAGER,
        ]);

        $creator = $this->auth->user();
        $categoryId = (int) ($post['category_id'] ?? 0);
        if (!$this->canManageCategory($creator['role_name'], $categoryId)) {
            http_response_code(403);
            echo 'Forbidden';
            exit;
        }

        $assetId = $this->assets->create($post, (int) $creator['id']);

        $officeAdminEmail = $this->users->officeAdministratorEmail() ?? $this->fallbackOfficeEmail;
        $this->notifications->notifyEntityCreated($officeAdminEmail, 'Asset', [
            'Asset ID' => (string) $assetId,
            'Serial Number' => $post['serial_number'] ?? '',
            'Category ID' => (string) ($post['category_id'] ?? ''),
        ], $creator['full_name']);

        header('Location: /index.php?action=assets');
        exit;
    }

    public function importCsv(array $files): void
    {
        $this->auth->requireRole([User::ROLE_SYSTEM_ADMINISTRATOR]);

        $file = $files['asset_csv'] ?? null;
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->redirectWithImportError('Please select a valid CSV file to upload.');
        }

        $tmpName = (string) ($file['tmp_name'] ?? '');
        $originalName = strtolower((string) ($file['name'] ?? ''));
        if ($tmpName === '' || !is_file($tmpName) || pathinfo($originalName, PATHINFO_EXTENSION) !== 'csv') {
            $this->redirectWithImportError('Only CSV files are supported for asset import.');
        }

        $handle = fopen($tmpName, 'rb');
        if ($handle === false) {
            $this->redirectWithImportError('Could not read the uploaded CSV file.');
        }

        try {
            $header = fgetcsv($handle);
            if ($header === false) {
                throw new RuntimeException('CSV file is empty.');
            }

            $headerMap = [];
            foreach ($header as $index => $columnName) {
                $normalized = strtolower(trim((string) $columnName));
                if ($normalized === '') {
                    continue;
                }

                $normalized = ltrim($normalized, "\xEF\xBB\xBF");
                $headerMap[$normalized] = $index;
            }

            foreach (['serial_number', 'category_id', 'status_id', 'date_of_purchase', 'supplier_id'] as $requiredColumn) {
                if (!isset($headerMap[$requiredColumn])) {
                    throw new RuntimeException(sprintf('Missing required CSV column: %s.', $requiredColumn));
                }
            }

            $validCategoryIds = [];
            foreach ($this->categories->all() as $category) {
                $validCategoryIds[(int) $category['id']] = true;
            }

            $validStatusIds = [];
            foreach ($this->assets->statuses() as $status) {
                $validStatusIds[(int) $status['id']] = true;
            }

            $validSupplierIds = [];
            foreach ($this->suppliers->all() as $supplier) {
                $validSupplierIds[(int) $supplier['id']] = true;
            }

            $validUserIds = [];
            foreach ($this->users->all() as $user) {
                $validUserIds[(int) $user['id']] = true;
            }

            $creator = $this->auth->user();
            $importedCount = 0;
            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if ($this->isEmptyCsvRow($row)) {
                    continue;
                }

                $serialNumber = trim((string) ($row[$headerMap['serial_number']] ?? ''));
                $dateOfPurchase = trim((string) ($row[$headerMap['date_of_purchase']] ?? ''));
                $categoryId = (int) trim((string) ($row[$headerMap['category_id']] ?? '0'));
                $statusId = (int) trim((string) ($row[$headerMap['status_id']] ?? '0'));
                $supplierId = (int) trim((string) ($row[$headerMap['supplier_id']] ?? '0'));
                $assignedToRaw = trim((string) ($row[$headerMap['assigned_to_user_id']] ?? ''));
                $assignedToUserId = $assignedToRaw === '' ? null : (int) $assignedToRaw;

                if ($serialNumber === '' || $dateOfPurchase === '' || $categoryId <= 0 || $statusId <= 0 || $supplierId <= 0) {
                    throw new RuntimeException(sprintf('Row %d has missing required values.', $rowNumber));
                }

                if (!isset($validCategoryIds[$categoryId])) {
                    throw new RuntimeException(sprintf('Row %d has an invalid category_id.', $rowNumber));
                }

                if (!isset($validStatusIds[$statusId])) {
                    throw new RuntimeException(sprintf('Row %d has an invalid status_id.', $rowNumber));
                }

                if (!isset($validSupplierIds[$supplierId])) {
                    throw new RuntimeException(sprintf('Row %d has an invalid supplier_id.', $rowNumber));
                }

                if ($assignedToUserId !== null && !isset($validUserIds[$assignedToUserId])) {
                    throw new RuntimeException(sprintf('Row %d has an invalid assigned_to_user_id.', $rowNumber));
                }

                if (!$this->isValidDate($dateOfPurchase)) {
                    throw new RuntimeException(sprintf('Row %d has an invalid date_of_purchase. Use YYYY-MM-DD.', $rowNumber));
                }

                $this->assets->create([
                    'serial_number' => $serialNumber,
                    'category_id' => $categoryId,
                    'status_id' => $statusId,
                    'date_of_purchase' => $dateOfPurchase,
                    'supplier_id' => $supplierId,
                    'assigned_to_user_id' => $assignedToUserId,
                    'specifications' => [],
                ], (int) ($creator['id'] ?? 0));
                $importedCount++;
            }

            if ($importedCount === 0) {
                throw new RuntimeException('No asset records were found in the CSV file.');
            }

            header('Location: /index.php?action=assets&import_success=' . rawurlencode(sprintf('%d assets imported successfully.', $importedCount)));
            exit;
        } catch (RuntimeException $exception) {
            $this->redirectWithImportError($exception->getMessage());
        } catch (Throwable $exception) {
            $this->redirectWithImportError('Asset import failed. Please check the CSV values and try again.');
        } finally {
            fclose($handle);
        }
    }

    public function specificationsByCategory(int $categoryId): void
    {
        $this->auth->requireRole([
            User::ROLE_SYSTEM_ADMINISTRATOR,
            User::ROLE_OFFICE_ADMINISTRATOR,
            User::ROLE_IT_MANAGER,
        ]);

        header('Content-Type: application/json');
        echo json_encode($this->categories->specificationDefinitionsByCategoryId($categoryId), JSON_THROW_ON_ERROR);
        exit;
    }

    private function canManageCategory(string $roleName, int $categoryId): bool
    {
        if ($roleName === User::ROLE_SYSTEM_ADMINISTRATOR) {
            return true;
        }

        $categoryNamesById = $this->categoryNameLookup();

        $categoryName = $categoryNamesById[$categoryId] ?? '';
        if ($roleName === User::ROLE_OFFICE_ADMINISTRATOR) {
            return $categoryName === 'Furniture';
        }

        if ($roleName === User::ROLE_IT_MANAGER) {
            return in_array($categoryName, ['Computers', 'Electrical Equipment'], true);
        }

        return false;
    }

    /** @return array<int, string> */
    private function categoryNameLookup(): array
    {
        if ($this->categoryNamesById !== null) {
            return $this->categoryNamesById;
        }

        $this->categoryNamesById = [];
        foreach ($this->categories->all() as $category) {
            $this->categoryNamesById[(int) $category['id']] = $category['name'];
        }

        return $this->categoryNamesById;
    }

    /** @param array<int, string|null> $row */
    private function isEmptyCsvRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function isValidDate(string $date): bool
    {
        $value = DateTimeImmutable::createFromFormat('Y-m-d', $date);

        return $value !== false && $value->format('Y-m-d') === $date;
    }

    private function redirectWithImportError(string $message): void
    {
        header('Location: /index.php?action=assets&import_error=' . rawurlencode($message));
        exit;
    }
}
