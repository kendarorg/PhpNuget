<?php
/**
 * Bridge between the uifw UI/auth layer (global classes, GlobalRegistry, PDO)
 * and PhpNuget's namespaced nuget data layer (lib\..., OminousFactory, mysqli).
 *
 * uifw never talks to lib\nuget directly except through this file: a UI-facing
 * API (e.g. api/packages.php) requires it, then reads packages via OminousFactory.
 * Both worlds point at the SAME MySQL database (the uifw config/config.php creds).
 *
 * Lowercase filename on purpose: GlobalRegistry::preLoadClasses only auto-loads
 * Capitalized framework classes, so this helper is included explicitly, not eagerly.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use lib\OminousFactory;

/**
 * Wire PhpNuget's Properties + a shared mysqli from uifw's MySQL registry values,
 * so OminousFactory builds its MySqlDbStorage against the same database. Idempotent.
 */
function nugetBridgeBootstrap()
{
    static $mysqli = null;
    if ($mysqli !== null) {
        return $mysqli;
    }

    $hostRaw = GlobalRegistry::get('DB_HOST');           // "host" or "host:port"
    $parts   = explode(':', $hostRaw);
    $host    = $parts[0];
    $port    = isset($parts[1]) ? (int)$parts[1] : 3306;
    $name    = GlobalRegistry::get('DB_NAME');
    $user    = GlobalRegistry::get('DB_USER');
    $pass    = GlobalRegistry::get('DB_PASS');

    $props = OminousFactory::getObject('properties');
    $props->setProperty('dbtype', 'mysql');
    $props->setProperty('db.host', $host);
    $props->setProperty('db.port', $port);
    $props->setProperty('db.name', $name);
    $props->setProperty('db.user', $user);
    $props->setProperty('db.password', $pass);

    $mysqli = new mysqli($host, $user, $pass, $name, $port);
    OminousFactory::setObject('mysqli', $mysqli);
    return $mysqli;
}

/**
 * @return \lib\nuget\NugetPackages the nuget package store on the shared MySQL DB
 */
function nugetPackages()
{
    nugetBridgeBootstrap();
    return OminousFactory::getObject('nugetpackages');
}

/**
 * Resolve a nuget user (merged into the uifw `users` table) by upload API key.
 * @return array|null the users row, or null when the key matches no user
 */
function nugetUserByApiKey($apiKey)
{
    $mysqli = nugetBridgeBootstrap();
    $stmt = $mysqli->prepare('SELECT id, username, email, role, locked FROM users WHERE apiKey = ? LIMIT 1');
    $stmt->bind_param('s', $apiKey);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}
