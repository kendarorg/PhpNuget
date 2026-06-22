<?php

if (!file_exists(__DIR__ . '/config/config.php')) {
    header('Location: setup/setup.php');
    exit;
}

// Include required files
require_once 'config.inc';
$audit =GlobalRegistry::get('Auth');

// Check if user is logged in
if (!$audit->isUserLoggedIn()) {
    // Redirect to login page
    header('Location: ui/login.html');
    exit;
} else {
    // Redirect to dashboard
    header('Location: ui/dashboard.html');
    exit;
}