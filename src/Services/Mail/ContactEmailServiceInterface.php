<?php

declare(strict_types=1);

namespace App\Services\Mail;

use App\Message\SendEmailMessage;

interface ContactEmailServiceInterface
{
    public function send(SendEmailMessage $sendEmailMessage): void;
}
