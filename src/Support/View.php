<?php

declare(strict_types=1);

namespace App\Support;

final class View
{
    public static function render(string $template, array $data = []): void
    {
        $templateFile = __DIR__ . '/../../views/' . $template . '.php';
        $viewData = [];
        foreach ($data as $key => $value) {
            if (!is_string($key) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
                continue;
            }

            if (in_array($key, ['templateFile', 'viewData'], true)) {
                continue;
            }

            $viewData[$key] = $value;
        }

        require __DIR__ . '/../../views/layouts/app.php';
    }
}
