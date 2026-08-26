<?php

declare(strict_types=1);

namespace App\Tests\Unit\ValueObject;

use App\ValueObject\PhoneNumber;
use PHPUnit\Framework\TestCase;

final class PhoneNumberTest extends TestCase
{
    public function testItNormalizesAndValidatesPhone(): void
    {
        // Проверяем нормализацию (пробелы и верхний регистр)
        $phone = PhoneNumber::fromString('  +7999 444 33 22 ');

        self::assertSame('+79994443322', $phone->getValue());
    }

    public function testItThrowsExceptionForInvalidPhone(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Некорректный номер');

        PhoneNumber::fromString('not-a phone');
    }
}
