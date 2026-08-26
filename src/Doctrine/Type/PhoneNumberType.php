<?php

declare(strict_types=1);

namespace App\Doctrine\Type;

use App\ValueObject\PhoneNumber;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class PhoneNumberType extends Type
{
    public const NAME = 'phone_number';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getStringTypeDeclarationSQL($column);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?PhoneNumber
    {
        if($value === null)
        {
            return null;
        }
        return PhoneNumber::fromString($value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if($value === null)
        {
            return null;
        }
        return $value->getValue();
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
