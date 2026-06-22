<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


require_once("utils/utils.php");
require_once("utils/logManager.php");
require_once("utils/globalRegistry.php");

//LogManager::setLoggerLevel("Translate",LogLevel::TRACE);
//LogManager::setLoggerLevel("GlobalRegistry",LogLevel::TRACE);
LogManager::setLoggerLevel("GlobalRegistry",LogLevel::ERROR);

GlobalRegistry::registerValue('DEFAULT_LANGUAGE', "it");
// Path settings
GlobalRegistry::registerValue('API_URL', "../api");
GlobalRegistry::registerValue('ROOT_PATH', __DIR__);
GlobalRegistry::registerValue('API_PATH', ROOT_PATH . '/api');
GlobalRegistry::registerValue('REPORT_PATH', ROOT_PATH . '/api/reports');
GlobalRegistry::registerValue('CONFIG_PATH', ROOT_PATH . '/config');
GlobalRegistry::registerValue('LIB_PATH', ROOT_PATH . '/lib');
GlobalRegistry::registerValue('MODELS_PATH', ROOT_PATH . '/models');
GlobalRegistry::registerValue('UI_PATH', ROOT_PATH . '/ui');
GlobalRegistry::registerValue('UTILS_PATH', ROOT_PATH . '/utils');
GlobalRegistry::registerValue('TRANSLATIONS_PATH', ROOT_PATH . '/translations');
GlobalRegistry::registerValue('BLOB_PATH', ROOT_PATH . '/blob');

GlobalRegistry::registerValue('CURRENT_OPERATION', generateUuidV7());

require_once("config/config.php");
require_once("config/company_config.php");

GlobalRegistry::preLoadClasses('CONFIG_PATH','UTILS_PATH','LIB_PATH','MODELS_PATH','API_PATH','UI_PATH');
