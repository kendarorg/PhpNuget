<?php
/**
 * NuGet service wiring — replaces the old lib\OminousFactory object graph with
 * lazy GlobalRegistry factory registrations. Loaded eagerly by
 * GlobalRegistry::preLoadClasses() (the `_load` suffix), but every service is
 * built lazily on first GlobalRegistry::get()/getByName().
 *
 * The uifw bridge (nugetbridge.php) may override db.* Properties and inject its
 * own shared mysqli via GlobalRegistry::setInstance('mysqli', ...); standalone
 * NuGet entrypoints (api/, upload/) fall back to conf/properties.json below.
 */

// Initialize the static Properties store from conf/properties.json. This file is
// pulled in via require_once (by GlobalRegistry::preLoadClasses), so it runs once.
// (Properties::initialize must not be called twice — the second call wipes it.)
$__propsFile = __DIR__ . '/../conf/properties.json';
if (file_exists($__propsFile)) {
    Properties::initialize($__propsFile);
}

GlobalRegistry::registerFactory('properties', function () {
    return new Properties();
});

GlobalRegistry::registerFactory('mysqli', function () {
    // Build a shared mysqli from the nuget Properties (conf/properties.json).
    // The uifw bridge may instead setInstance('mysqli', ...) with its own connection;
    // either way the whole app shares ONE mysql connection.
    $properties = GlobalRegistry::get('properties');
    $host = $properties->getProperty('db.host', 'localhost');
    $port = intval($properties->getProperty('db.port', 3306));
    $user = $properties->getProperty('db.user');
    $pass = $properties->getProperty('db.password');
    $name = $properties->getProperty('db.name');
    return new \mysqli($host, $user, $pass, $name, $port);
});

GlobalRegistry::registerFactory('nugetdownloads', function () {
    return new NugetDownloads();
});

GlobalRegistry::registerFactory('request', function () {
    return new Request();
});

GlobalRegistry::registerFactory('nugetusersstorage', function () {
    $properties = GlobalRegistry::get('properties');
    $dbType = $properties->getProperty('dbtype', 'file');
    if ($dbType == 'mysql') {
        return new MySqlDbStorage($properties, new QueryParser(), null, GlobalRegistry::get('mysqli'));
    }
    return new FileDbStorage($properties, new QueryParser());
});

GlobalRegistry::registerFactory('nugetusers', function () {
    return new NugetUsers(GlobalRegistry::get('nugetusersstorage'));
});

GlobalRegistry::registerFactory('nugetpackagesstorage', function () {
    $properties = GlobalRegistry::get('properties');
    $dbType = $properties->getProperty('dbtype', 'file');
    if ($dbType == 'mysql') {
        return new MySqlDbStorage($properties, new QueryParser(), null, GlobalRegistry::get('mysqli'), new NugetPackageConverter());
    }
    return new FileDbStorage($properties, new QueryParser());
});

GlobalRegistry::registerFactory('nugetpackages', function () {
    return new NugetPackages(GlobalRegistry::get('nugetpackagesstorage'), GlobalRegistry::get('properties'));
});

GlobalRegistry::registerFactory('resourcesloader', function () {
    return new ResourcesLoader(GlobalRegistry::getByName('resourcesLoaderVersion'));
});

GlobalRegistry::registerFactory('nugetqueryhandler', function () {
    return new NugetQueryHandler(GlobalRegistry::get('nugetpackages'));
});

GlobalRegistry::registerFactory('lastquerybuilder', function () {
    return new LastQueryBuilder();
});

GlobalRegistry::registerFactory('nugetresultparser', function () {
    return new NugetResultParser(GlobalRegistry::get('resourcesloader'), GlobalRegistry::get('lastquerybuilder'));
});
