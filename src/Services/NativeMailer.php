<?php

declare(strict_types=1);

namespace App\Services;

final class NativeMailer implements MailerInterface
{
    public function __construct(private readonly string $fromAddress, private readonly string $fromName)
    {
    }

    public function send(string $to, string $subject, string $body): bool
    {
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/plain; charset=UTF-8',
            sprintf('From: %s <%s>', $this->fromName, $this->fromAddress),
        ];

        return mail($to, $subject, $body, implode("\r\n", $headers));
    }
}
