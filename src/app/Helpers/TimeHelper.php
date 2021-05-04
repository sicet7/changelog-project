<?php

namespace App\Helpers;

class TimeHelper
{

    public static function convertTimezone(\DateTimeImmutable $dateTime ,string $toTimezone): \DateTimeImmutable
    {
        $input = \DateTime::createFromImmutable($dateTime);
        return \DateTimeImmutable::createFromMutable($input->setTimezone(new \DateTimeZone($toTimezone)));
    }

}