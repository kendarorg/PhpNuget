<?php
// Root entrypoint.
//   - A nuget client PUT (package push) is forwarded to the upload handler.
//   - Browsers are redirected to the uifw UI under /app.
// The nuget OData protocol lives under /api/** and is served independently.
require_once(__DIR__ . "/vendor/autoload.php");

if (strtolower($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'put') {
    require_once(__DIR__ . "/upload/index.php");
    die();
}

header('Location: app/');
exit;
