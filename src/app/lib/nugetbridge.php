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

/**
 * @return string the configured filesystem root where .nupkg/.snupkg files live
 */
function nugetPackagesRoot()
{
    nugetBridgeBootstrap();
    return OminousFactory::getObject('properties')->getProperty('packagesRoot');
}

/**
 * @return string the public site root (used to build feed URLs / install snippets)
 */
function nugetSiteRoot()
{
    nugetBridgeBootstrap();
    return OminousFactory::getObject('properties')->getProperty('siteRoot');
}

/**
 * Upsert a package, optionally re-pointing the "latest version" flags for the Id.
 * Editing an existing version passes $updateLatest=false; a fresh upload passes true.
 *
 * @param \lib\nuget\models\NugetPackage $pkg
 * @param bool $updateLatest
 * @return void
 */
function nugetPackageSave($pkg, $updateLatest = false)
{
    $store = nugetPackages();
    $store->save($pkg);

    if ($updateLatest) {
        $mysqli = nugetBridgeBootstrap();
        // the freshly pushed version becomes the latest; demote the rest of the Id.
        $clear = $mysqli->prepare('UPDATE `packages` SET `IsLatestVersion` = 0, `IsAbsoluteLatestVersion` = 0 WHERE `Id` = ?');
        $clear->bind_param('s', $pkg->Id);
        $clear->execute();
        $clear->close();

        $set = $mysqli->prepare('UPDATE `packages` SET `IsLatestVersion` = 1, `IsAbsoluteLatestVersion` = 1 WHERE `Id` = ? AND `Version` = ?');
        $set->bind_param('ss', $pkg->Id, $pkg->Version);
        $set->execute();
        $set->close();
    }
}

/**
 * Delete a single package version row and its on-disk files.
 * @return void
 */
function nugetPackageDelete($id, $version)
{
    nugetPackages()->delete($id, $version);

    $root = rtrim(nugetPackagesRoot(), '/');
    foreach (['.nupkg', '.snupkg'] as $ext) {
        $path = $root . '/' . $id . '.' . $version . $ext;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

/**
 * @return \lib\nuget\models\NugetPackage[] all versions of an Id, newest first
 */
function nugetPackageVersions($id)
{
    $safe = str_replace("'", '', (string)$id);
    return nugetPackages()->query("(Id eq '$safe') orderBy desc Version");
}

/**
 * @return \lib\nuget\models\NugetPackage[] the latest version of each package owned by $userId
 */
function nugetPackagesByOwner($userId, $sq)
{
    $clauses = ['(IsLatestVersion eq true)', '(UserId eq ' . (int)$userId . ')'];

    if (is_object($sq) && is_array($sq->searchTerms) && !empty($sq->searchTerms['search'])) {
        $term = str_replace("'", '', (string)$sq->searchTerms['search']);
        if ($term !== '') {
            $clauses[] = "(substringof('$term',Title) or substringof('$term',Id))";
        }
    }

    $query = implode(' and ', $clauses) . ' orderBy asc Id';
    $from  = is_object($sq) ? max(0, (int)$sq->from) : 0;
    $count = is_object($sq) ? (int)$sq->count : -1;
    if ($count <= 0) {
        $count = 50;
    }
    return nugetPackages()->query($query, $count, $from);
}

/**
 * Generate and persist a fresh upload API key for a user, returning it once.
 * Uppercased to match the upload endpoint's strtoupper() lookup.
 *
 * @return string the new key
 */
function nugetUserApiKeyRegenerate($userId)
{
    $mysqli = nugetBridgeBootstrap();
    $key = strtoupper(bin2hex(random_bytes(20)));
    $stmt = $mysqli->prepare('UPDATE users SET apiKey = ? WHERE id = ?');
    $stmt->bind_param('si', $key, $userId);
    $stmt->execute();
    $stmt->close();
    return $key;
}

/**
 * @return string|null the user's current upload API key
 */
function nugetUserApiKey($userId)
{
    $mysqli = nugetBridgeBootstrap();
    $stmt = $mysqli->prepare('SELECT apiKey FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? $row['apiKey'] : null;
}
