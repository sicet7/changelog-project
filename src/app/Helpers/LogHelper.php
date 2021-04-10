<?php


namespace App\Helpers;


use Psr\Container\ContainerInterface;
use Psr\Log\AbstractLogger;

class LogHelper extends AbstractLogger
{
    public const KEY = 'log.file';
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function log($level, $message, array $context = array())
    {
        $file = $this->getFilePath();
        $logDir = dirname($file);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        file_put_contents(
            $file,
            $this->getTimestamp() . ' ' . $this->formatMessage($level, $message, $context) . PHP_EOL,
            FILE_APPEND | LOCK_EX
        );
    }

    public function formatMessage($level, $message, array $context = array())
    {
        return strtoupper($level) . ' : ' . $message;
    }

    public function getFilePath()
    {
        return $this->container->get(self::KEY);
    }

    public function getTimestamp()
    {
        return date('c');
    }
}