<?php


//Will be removed when it will be relly existing
function translate(...$args){
    $translator = GlobalRegistry::get("Translator");
    return $translator->translate(...$args);
}

function translateHt(...$args){
    $translator = GlobalRegistry::get("Translator");
    return $translator->translateHt(...$args);
}
function generateUuid()
{
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
}

function generateUuidV7()
{
    $time = (int) floor(microtime(true) * 1000); // milliseconds

    // 48-bit timestamp
    $timeHex = str_pad(dechex($time), 12, '0', STR_PAD_LEFT);

    // 10 random bytes (80 bits)
    $random = random_bytes(10);
    $randHex = bin2hex($random);

    // Split random parts
    $randA = substr($randHex, 0, 4);
    $randB = substr($randHex, 4, 4);
    $randC = substr($randHex, 8, 12);

    // Set version (7)
    $randA = dechex((hexdec($randA) & 0x0fff) | 0x7000);

    // Set variant (RFC4122)
    $randB = dechex((hexdec($randB) & 0x3fff) | 0x8000);

    return sprintf(
        '%s-%s-%s-%s-%s',
        substr($timeHex, 0, 8),
        substr($timeHex, 8, 4),
        str_pad($randA, 4, '0', STR_PAD_LEFT),
        str_pad($randB, 4, '0', STR_PAD_LEFT),
        str_pad($randC, 12, '0', STR_PAD_LEFT)
    );
}

//getOrDefaultMany('REQUEST_METHOD', [$_SERVER], 'GET');
function getOrDefaultMany($key, $arrays, $default = null) {

    if(array_key_exists($key, $arrays)){
        return $arrays[$key];
    }
    foreach ($arrays as $array) {
        if (is_array($array) && array_key_exists($key, $array)) {
            return $array[$key];
        }
    }
    return $default;
}

function getOrDefaultManyNotNull($key, $arrays, $default = null) {
    if(array_key_exists($key, $arrays)){
        if($arrays[$key]==null || $arrays[$key]==""){
            return $default;
        }
        return $arrays[$key];
    }
    foreach ($arrays as $array) {
        if (is_array($array) && array_key_exists($key, $array)) {
            if($arrays[$key]==null || $arrays[$key]==""){
                continue;
            }else {
                return $arrays[$key];
            }
        }
    }
    return $default;
}

function getDbConnection()
{

    $dsn = "mysql:host=" . GlobalRegistry::get('DB_HOST') . ";dbname=" . GlobalRegistry::get('DB_NAME') . ";charset=" .
        GlobalRegistry::get('DB_CHARSET');
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    return new PDO($dsn, GlobalRegistry::get('DB_USER'), GlobalRegistry::get('DB_PASS'), $options);
}

function getOrNull(&$array,...$keys){
    $current = &$array;
    foreach ($keys as $key) {
        if(array_key_exists($key, $current)){
            $current = &$current[$key];
        }else{
            return null;
        }
    }
    return $current;
}