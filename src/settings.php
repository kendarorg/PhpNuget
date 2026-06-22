<?php
// Compatibility shim. The real bootstrap now lives in config.inc, which sets up
// GlobalRegistry (its autoloader + path values) and the NuGet service wiring
// (lib/nugetServices_load.php initializes Properties from conf/properties.json).
// Kept only so legacy assets/views/*.php that `require_once .../settings.php`
// continue to get a fully bootstrapped environment.
require_once(__DIR__ . "/config.inc");
