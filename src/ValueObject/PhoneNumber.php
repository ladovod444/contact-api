<?php

declare(strict_types=1);

namespace App\ValueObject;

final class PhoneNumber
{
    private function __construct(private readonly string $value) {}

    public static function fromString(string $phone): self
    {
        $normalized = trim($phone);

        // Используем тот же regex, что был в вашем DTO
        if(!preg_match('/^\+?[0-9\s\-()]{10,20}$/', $normalized))
        {
            throw new \InvalidArgumentException(sprintf('Некорректный номер телефона: "%s"', $phone));
        }

        // удалить все пробелы и дефисы
        $normalized = preg_replace('/[\s\-()]/', '', $normalized);

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
