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

    /**
     * @param string $requestUri
     * @param Properties $properties
     * @return string
     */
    public static function currentUrl($requestUri = "",$properties) {
        $pageURL = 'http';
        $isHttps = false;
        if ((array_key_exists("HTTPS",$_SERVER) && $_SERVER["HTTPS"] == "on") ||
            (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) && $_SERVER["HTTP_X_FORWARDED_PROTO"] = "https")) {
            $pageURL .= "s";
            $isHttps = true;
        }

        $pageURL .= "://";
        if($requestUri==""){
            $requestUri = $properties->getProperty("siteRoot",$_SERVER["REQUEST_URI"]);
        }else{
            $rootUri = rtrim($properties->getProperty("siteRoot",$_SERVER["REQUEST_URI"]),"\\/");
            $requestUri = $rootUri."/".ltrim($requestUri,"\\/");

        }
        $requestUri = trim($requestUri,"\\/");
        if ($_SERVER["SERVER_PORT"] != "80" && !$isHttps ) {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]."/".$requestUri;
        } else if ($_SERVER["SERVER_PORT"] != "433" && $isHttps ) {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"]."/".$requestUri;
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"]."/".$requestUri;
        }
        return $pageURL;
    }
}