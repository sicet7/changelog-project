<?php

namespace App\Database;

use Doctrine\ORM\Mapping\ClassMetadata;

interface EntityInterface
{
    /**
     * @param ClassMetadata $metadata
     * @return void
     */
    public static function loadMetadata(ClassMetadata $metadata);
}