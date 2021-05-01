<?php

namespace App\Helpers;

class TimeHelper
{

    public static function convertTimezone(\DateTimeImmutable $dateTime, string $fromTimezone ,string $toTimezone): \DateTimeImmutable
    {
        $input = \DateTime::createFromImmutable($dateTime);
        $input->setTimezone(new \DateTimeZone($fromTimezone));
        $target = new \DateTimeZone($toTimezone);
        return \DateTimeImmutable::createFromMutable(
            $input->add(new \DateInterval('PT' . $target->getOffset($input) . 'S'))
        );
    }

}