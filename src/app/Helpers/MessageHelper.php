<?php

namespace App\Helpers;

class MessageHelper
{
    private const KEY = 'messages';

    private function createSession()
    {
        if (!isset($_SESSION[self::KEY]) || !is_array($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = [];
        }
    }

    /**
     * @param string $key
     * @param string $message
     */
    public function addMessage(string $key, string $message)
    {
        $this->createSession();
        $_SESSION[self::KEY][$key] = $message;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getMessage(string $key)
    {
        $this->createSession();
        if (empty($_SESSION[self::KEY])) {
            return null;
        }
        return (isset($_SESSION[self::KEY][$key]) ? $_SESSION[self::KEY][$key] : null);
    }

    /**
     * @return array
     */
    public function getAllMessages()
    {
        $this->createSession();
        return $_SESSION[self::KEY];
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasMessage(string $key)
    {
        $this->createSession();
        return isset($_SESSION[self::KEY][$key]);
    }

    /**
     * @return void
     */
    public function reset()
    {
        $_SESSION[self::KEY] = [];
    }
}