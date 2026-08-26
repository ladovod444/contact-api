<?php

declare(strict_types=1);

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\EmailAddress;
use PHPUnit\Framework\TestCase;

final class EmailAddressTest extends TestCase
{
    public function testItNormalizesAndValidatesEmail(): void
    {
        // Проверяем нормализацию (пробелы и верхний регистр)
        $email = EmailAddress::fromString('  PETYA@Example.com ');

        self::assertSame('petya@example.com', $email->getValue());
    }

    public function testItThrowsExceptionForInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Некорректный email');

        EmailAddress::fromString('not-an-email');
    }
}
