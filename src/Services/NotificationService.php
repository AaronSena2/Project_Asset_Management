<?php

declare(strict_types=1);

namespace App\Services;

final class NotificationService
{
    public function __construct(private readonly MailerInterface $mailer)
    {
    }

    public function notifyEntityCreated(string $officeAdminEmail, string $entityType, array $summary, string $createdBy): void
    {
        $subject = sprintf('[Inventory] New %s Created', $entityType);
        $bodyLines = [
            sprintf('A new %s has been created.', $entityType),
            sprintf('Created by: %s', $createdBy),
            'Summary:',
        ];

        foreach ($summary as $key => $value) {
            $bodyLines[] = sprintf('- %s: %s', $key, $value);
        }

        $this->mailer->send($officeAdminEmail, $subject, implode(PHP_EOL, $bodyLines));
    }
}
