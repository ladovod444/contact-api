<?php

declare(strict_types=1);

namespace App\ValueObject;

final class EmailAddress
{
    private function __construct(private readonly string $value) {}

    /**
     * Именованный конструктор (Factory Method)
     */
    public static function fromString(string $email): self
    {
        // 1. Нормализация: убираем пробелы и приводим к нижнему регистру
        $normalized = trim(strtolower($email));

        // 2. Валидация инварианта: если email невалиден, объект просто не будет создан
        if(!filter_var($normalized, FILTER_VALIDATE_EMAIL))
        {
            throw new \InvalidArgumentException(sprintf('Некорректный email: "%s"', $email));
        }

        return new self($normalized);
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
