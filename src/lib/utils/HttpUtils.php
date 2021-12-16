<?php

namespace lib\utils;

class HttpUtils
{
    public static function getSslPage($url) {

        if ( defined('__HTTPPROXY__') && (__HTTPPROXY__ !== '')) {
            $proxy = __HTTPPROXY__;
        } elseif (getenv('http_proxy')) {
            $proxy = getenv('http_proxy');
        } else {
            $proxy = null;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}