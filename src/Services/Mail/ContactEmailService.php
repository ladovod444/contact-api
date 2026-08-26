<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Message\SendEmailMessage;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Отправляет письмо владельцу сайта и копию отправителю
 */
#[WithMonologChannel('contact')]
class ContactEmailService implements ContactEmailServiceInterface
{
    function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $siteEmail,
        private readonly LoggerInterface $logger,
    ) {}

    public function send(SendEmailMessage $sendEmailMessage): void
    {
        $email = new TemplatedEmail()
            ->from($this->siteEmail)
            ->to($this->siteEmail)
            ->cc($sendEmailMessage->getEmail())
            ->subject('Contact')
            ->htmlTemplate('email/contact.html.twig')
            ->context([
                'name' => $sendEmailMessage->getName(),
                'phone' => $sendEmailMessage->getPhone(),
                'comment' => $sendEmailMessage->getComment(),
                'autoReply' => $sendEmailMessage->getAutoReply() ?? '',
            ]);

        $this->mailer->send($email);

        $this->logger->info("Письмо успешно поставлено в очередь отправки", [
            'to' => $sendEmailMessage->getEmail(),
        ]);
    }
}
