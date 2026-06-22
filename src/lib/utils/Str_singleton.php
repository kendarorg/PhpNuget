<?php

class Str
{
    static function indexOf($where, $what)
    {
        $res = strpos($where, $what);
        if ($res === false) return -1;
        return $res;
    }

    static function substr($string, $startAt = 0, $length = -1)
    {
        if ($length === -1) return substr($string, $startAt);
        return substr($string, $startAt, $length);
    }

    static function trim(string $input, ...$chars): string
    {
        if (sizeof($chars) === 0) {
            return preg_replace('/^\s+|\s+$/u', '', $input);
        }
        $pattern = '/^[' . preg_quote(implode('', $chars), '/') . ']+|[' . preg_quote(implode('', $chars), '/') . ']+$/u';
        return preg_replace($pattern, '', $input);
    }

    static function ltrim(string $input, ...$chars): string
    {
        if (sizeof($chars) === 0) {
            return preg_replace('/^\s+/u', '', $input);
        }
        $pattern = '/^[' . preg_quote(implode('', $chars), '/') . ']+/u';
        return preg_replace($pattern, '', $input);
    }

    static function rtrim(string $input, ...$chars): string
    {
        if (sizeof($chars) === 0) {
            return preg_replace('/^\s+$/u', '', $input);
        }
        $pattern = '/^[' . preg_quote(implode('', $chars), '/') . ']+$/u';
        return preg_replace($pattern, '', $input);
    }

}
