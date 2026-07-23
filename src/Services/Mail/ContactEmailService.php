<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\DTO\AiAnalysisDTO;
use App\DTO\ContactDTO;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * Отправляет письмо владельцу сайта и копию отправителю
 */
#[WithMonologChannel('contact')]
class ContactEmailService implements ContactEmailServiceInterface
{
    function __construct(
        private MailerInterface $mailer,
        private string $siteEmail,
        private LoggerInterface $logger,
    ) {}

    public function send(AiAnalysisDTO $analysisDTO, ContactDTO $contactDTO): void
    {

        $email = new TemplatedEmail()
            ->from($this->siteEmail)
            ->to($this->siteEmail)
            ->cc($contactDTO->getEmail())
            ->subject('Contact')
            ->htmlTemplate('email/contact.html.twig')

            // Передать переменный в template
            ->context([
                'name' => $contactDTO->getName(),
                'phone' => $contactDTO->getPhone(),
                'comment' => $contactDTO->getComment(),
                'autoReply' => $analysisDTO->getAutoReply() ?? '',
            ]);

        try
        {
            $this->mailer->send($email);
            $this->logger->info("Отправлено письмо контакты: ", [
                'to' => $contactDTO->getEmail(),
                'copy' => $this->siteEmail,
                'message' => [
                    'name' => $contactDTO->getName(),
                    'phone' => $contactDTO->getPhone(),
                    'comment' => $contactDTO->getComment(),
                    'autoReply' => $analysisDTO->getAutoReply() ?? '',
                ],
            ]);
        }
        catch(TransportExceptionInterface $e)
        {
            // Логировать ошибку с контекстом, чтобы потом можно было разобраться
            $this->logger->error(
                'Ошибка при отправке email',
                [
                    'exception' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'email_to' => $contactDTO->getEmail(),
                ]
            );
        }

    }
}
