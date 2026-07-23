<?php

declare(strict_types=1);

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class ContactDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Имя обязательно для заполнения')]
        #[Assert\Length(min: 3, minMessage: 'Имя должно содержать не меньше 3 символов')]
        private string $name,

        #[Assert\NotBlank(message: 'Телефон обязателен для заполнения')]
        #[Assert\Regex(
            pattern: '/^\+?[0-9\s\-()]{10,20}$/',
            message: 'Некорректный формат телефона. Допускаются цифры, пробелы, дефисы и скобки.'
        )]
        private string $phone,

        #[Assert\NotBlank(message: 'Email обязателен для заполнения')]
        #[Assert\Email(message: 'Некорректный формат email')]
        private string $email,

        #[Assert\NotBlank(message: 'Комментарий обязателен для заполнения')]
        #[Assert\Length(max: 2000, maxMessage: 'Комментарий не должен превышать 2000 символов')]
        private string $comment,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
