<?php

namespace App\Type;

use Doctrine\DBAL\Types\Type;
use \Doctrine\DBAL\Platforms\AbstractPlatform;

class DateKey extends \DateTime 
{
    public function __toString()
    {
        return $this->format('Y-m-d');
    }

    static function fromDateTime(\DateTime $dateTime) {
        return new static($dateTime->format('Y-m-d'));
    }
}
 

class DateKeyType extends Type {

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // return the SQL used to create your column type. To create a portable column type, use the $platform.
        return 'DATE';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // This is executed when the value is read from the database. Make your conversions here, optionally using the $platform.
        print('TODO');
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        // This is executed when the value is written to the database. Make your conversions here, optionally using the $platform.
        return $value->format('Y-m-d');
    }

    public function getName()
    {
        return 'datekey';
    }
}
