<?php
/**
 * Nuget package upload endpoint (`/upload`). Receives a .nupkg/.snupkg via
 * `dotnet nuget push` (multipart field "package", header X-NuGet-ApiKey) or the
 * uifw Upload page (same contract), parses it with NugetFileParser and
 * persists the row through NugetPackages. The uploading user is resolved
 * by API key against the merged uifw `users` table.
 */
require_once(dirname(__DIR__) . "/config.inc");


$request    = GlobalRegistry::get("request");
$properties = GlobalRegistry::get("properties");

function uploadError($code, $message)
{
    http_response_code($code);
    header('Content-Type: text/plain');
    error_log("[upload] $code $message");
    echo $message;
    exit;
}

$destination = null;
try {
    // --- Authenticate by API key (merged users table) ---
    if (empty($_SERVER['HTTP_X_NUGET_APIKEY'])) {
        uploadError(403, 'Invalid API key');
    }
    $token = strtoupper(trim(trim($_SERVER['HTTP_X_NUGET_APIKEY'], "{"), "}"));

    $mysqli = new mysqli(
        $properties->getProperty("db.host"),
        $properties->getProperty("db.user"),
        $properties->getProperty("db.password"),
        $properties->getProperty("db.name"),
        intval($properties->getProperty("db.port", 3306))
    );
    $stmt = $mysqli->prepare("SELECT id, locked FROM users WHERE apiKey = ? LIMIT 1");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $userRow = $stmt->get_result()->fetch_assoc();
    if (!$userRow || intval($userRow['locked']) === 1) {
        uploadError(403, 'Invalid API key');
    }
    $userId = $userRow['id'];

    // --- Receive the file (field name "package", as nuget push sends) ---
    $uploads = new UploadManager($request);
    $file = $uploads->getFile("package");
    if ($file === null) {
        // fall back to the first uploaded file (web form may use a different field name)
        $names = $uploads->listFiles();
        if (count($names) > 0) {
            $file = $uploads->getFile($names[0]);
        }
    }
    if ($file === null) {
        uploadError(400, 'No package file received');
    }
    if ($file->hasError()) {
        uploadError(500, 'Upload error: ' . $file->errorMessage);
    }

    $isSymbol = stripos($file->name, ".snupkg") !== false
        || stripos($file->name, ".symbols.") !== false
        || $request->getParam("symbol", null) != null;

    // --- Stage to a temp file so we can parse before final placement ---
    $tmp = tempnam(sys_get_temp_dir(), 'nupkg');
    if (!$file->saveFile($tmp)) {
        uploadError(500, 'Unable to store uploaded file');
    }

    $parser = new NugetFileParser($properties);
    $package = $parser->loadNupkg($tmp);
    $package->UserId = $userId;

    // --- Final placement: <packagesRoot>/<Id>.<Version>.(nupkg|snupkg) ---
    $root = rtrim($properties->getProperty("packagesRoot"), '/');
    if (!is_dir($root)) {
        @mkdir($root, 0775, true);
    }
    $ext = $isSymbol ? ".snupkg" : ".nupkg";
    $destination = $root . '/' . $package->Id . '.' . $package->Version . $ext;
    if (!rename($tmp, $destination)) {
        @unlink($tmp);
        uploadError(500, 'Unable to place package file');
    }

    // --- Persist. Symbol packages do not create a package row. ---
    if (!$isSymbol) {
        // upload runs outside the uifw GlobalRegistry; talk to OminousFactory directly.
        GlobalRegistry::get('nugetpackages')->save($package);

        // promote this version to "latest" and demote the others of this Id.
        $clear = $mysqli->prepare('UPDATE `packages` SET `IsLatestVersion` = 0, `IsAbsoluteLatestVersion` = 0 WHERE `Id` = ?');
        $clear->bind_param('s', $package->Id);
        $clear->execute();
        $set = $mysqli->prepare('UPDATE `packages` SET `IsLatestVersion` = 1, `IsAbsoluteLatestVersion` = 1 WHERE `Id` = ? AND `Version` = ?');
        $set->bind_param('ss', $package->Id, $package->Version);
        $set->execute();
    }

    header('HTTP/1.1 201 Created');
    header('Content-Type: text/plain');
    echo $package->Id . ' ' . $package->Version;
} catch (Exception $ex) {
    if ($destination !== null && is_file($destination)) {
        @unlink($destination);
    }
    uploadError(500, $ex->getMessage());
}
