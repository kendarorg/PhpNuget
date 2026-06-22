<?php
require_once("../config.inc");

$auth = GlobalRegistry::get('Auth');

$values = GlobalRegistry::$globalRegistryValues;
unset($values['db_host'], $values['db_name'], $values['db_user'], $values['db_pass'], $values['db_charset']);

$values['logged_in'] = $auth->isUserLoggedIn();
if ($values['logged_in']) {
    $u = $auth->getUser();
    $displayName = (isset($u['ragioneSociale']) && $u['ragioneSociale']) ? $u['ragioneSociale'] : $u['username'];
    $values['user'] = [
        'id'           => $u['id'],
        'display_name' => $displayName,
        'role'         => $u['role'],
    ];
    $values['menu'] = buildMenu($auth);
} else {
    $values['user'] = null;
    $values['menu'] = [];
}

if (isset($_GET['permissions'])) {
    $values['permissions'] = loadPerms($auth, $_GET['permissions']);
}

if (isset($_GET['permissions2'])) {
    $values['permissions2'] = loadPerms($auth, $_GET['permissions2']);
}

header('Content-Type: application/json');
echo json_encode($values);

function loadPerms($auth, $module) {
    $p = $auth->loadPermissions($module);
    return [
        'can_read'        => (bool)$p->canRead(),
        'can_create'      => (bool)$p->canCreate(),
        'can_delete'      => (bool)$p->canDelete(),
        'can_update'      => method_exists($p, 'canUpdate')     ? (bool)$p->canUpdate()     : (bool)$p->canCreate(),
        'can_access'      => method_exists($p, 'canAccess')     ? (bool)$p->canAccess()     : (bool)$p->canRead(),
        'can_create_own'  => method_exists($p, 'canCreateOwn')  ? (bool)$p->canCreateOwn()  : false,
        'can_read_own'    => method_exists($p, 'canReadOwn')    ? (bool)$p->canReadOwn()    : false,
    ];
}

function buildMenu($auth) {
    // PhpNuget UI menu. 'packages' is the gallery (visible to any reader);
    // the rest are the uifw admin modules.
    $items = [
        ['key' => 'dashboard',       'perm' => 'dashboard',       'page' => 'dashboard.html',       'class' => 'btn-dashboard', 'always' => true],
        ['key' => 'packages',        'perm' => 'packages',        'page' => 'packages.html',        'class' => 'btn-documents'],
        ['key' => 'users',           'perm' => 'users',           'page' => 'users.html',           'class' => 'btn-users'],
        ['key' => 'roles',           'perm' => 'roles',           'page' => 'roles.html',           'class' => 'btn-roles'],
        ['key' => 'functionalities', 'perm' => 'functionalities', 'page' => 'functionalities.html', 'class' => 'btn-multichoice'],
        ['key' => 'maintenance',     'perm' => 'maintenance',     'page' => 'maintenance.html',     'class' => 'btn-maintenance'],
    ];

    $visible = [];
    foreach ($items as $item) {
        if (!empty($item['always'])) {
            $visible[] = ['key' => $item['key'], 'page' => $item['page'], 'class' => $item['class']];
            continue;
        }
        $p = $auth->loadPermissions($item['perm']);
        // read-centric modules (the gallery) show on read; managed modules on create.
        $canSee = $p->canRead() || (method_exists($p, 'canCreateOwn') && $p->canCreateOwn());
        if ($canSee) {
            $visible[] = ['key' => $item['key'], 'page' => $item['page'], 'class' => $item['class']];
        }
    }
    return $visible;
}
