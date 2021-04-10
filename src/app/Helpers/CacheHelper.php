<?php

namespace App\Helpers;

use JetBrains\PhpStorm\Pure;
use Psr\Container\ContainerInterface;

class CacheHelper
{
    private string $cacheDir;

    /**
     * @var Base64Helper
     */
    private Base64Helper $base64Helper;

    /**
     * CacheHelper constructor.
     * @param ContainerInterface $container
     * @param Base64Helper $base64Helper
     */
    public function __construct(
        ContainerInterface $container,
        Base64Helper $base64Helper
    ) {
        $this->cacheDir = $container->get('cache.path');
        $this->base64Helper = $base64Helper;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function contains(string $key): bool
    {
        return file_exists($this->buildFilePath($key));
    }

    /**
     * @param string $key
     * @return string
     */
    public function buildFilePath(string $key): string
    {
        return $this->cacheDir . '/cacheHelper/' . $this->base64Helper->urlsafeEncode($key) . '.cache';
    }

    /**
     * @param string $key
     * @param string $data
     * @param bool $compress
     * @return bool
     */
    public function save(string $key, string $data, bool $compress = true)
    {
        $file = $this->buildFilePath($key);
        $dir = dirname($file);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $data = ($compress ? $this->compress($data) : $data);
        return (file_put_contents($file, $data, LOCK_EX) !== false);
    }

    /**
     * @param string $key
     * @param bool $decompress
     * @return mixed
     */
    public function read(string $key, bool $decompress = true): mixed
    {
        if (!$this->contains($key)) {
            return false;
        }
        $file = $this->buildFilePath($key);
        $content = file_get_contents($file);
        if ($content === false) {
            return false;
        }
        if ($decompress) {
            return $this->decompress($content);
        }
        return $content;
    }

    #[Pure]
    public function compress(string $data): string
    {
        return base64_encode(gzcompress($data));
    }

    #[Pure]
    public function decompress(string $data)
    {
        return gzuncompress(base64_decode($data));
    }
}