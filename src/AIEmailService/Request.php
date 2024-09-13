<?php

namespace HercegDoo\AIComposePlugin\AIEmailService;

final class Request
{
    /**
     * @param null|array|string $default
     *
     * @return null|array|string
     */
    public static function post(string $key, $default = null)
    {
        return self::input($key, $default, \rcube_utils::INPUT_POST);
    }

    /**
     * @param null|array|string $default
     *
     * @return null|array|string
     */
    public static function get(string $key, $default = null)
    {
        return self::input($key, $default, \rcube_utils::INPUT_GET);
    }

    /**
     * @param null|string $default
     *
     * @return null|string
     */
    public static function postString(string $key, $default = null)
    {
        $data = self::post($key, $default);
        if (\is_array($data)) {
            return null;
        }

        return $data;
    }

    /**
     * @param null|array|string $default
     *
     * @return null|array|string
     */
    private static function input(string $key, $default = null, string $source = \rcube_utils::INPUT_POST)
    {
        $data = \rcube_utils::get_input_value($key, $source);
        $data = $data === '' ? null : $data;
        if ($data === null) {
            return $default;
        }

        return $data;
    }
}
