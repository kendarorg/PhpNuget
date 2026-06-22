<?php
// Root entrypoint.
//   - A nuget client PUT (package push) is forwarded to the upload handler.
//   - Browsers are routed into the uifw admin UI (now at this src root).
// The nuget OData protocol lives under /api/** and is served independently.

if (strtolower($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'put') {
    require_once(__DIR__ . "/upload/index.php");
    die();
}

// Not yet installed -> run the setup wizard.
if (!file_exists(__DIR__ . '/config/config.php')) {
    header('Location: setup/setup.php');
    exit;
}

// Bootstrap GlobalRegistry and route to login or dashboard.
require_once(__DIR__ . '/config.inc');
$auth = GlobalRegistry::get('Auth');

if (!$auth->isUserLoggedIn()) {
    header('Location: ui/login.html');
} else {
    header('Location: ui/dashboard.html');
}
exit;
