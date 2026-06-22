<?php
require_once("../config.inc");
require_once(__DIR__ . "/../lib/nugetbridge.php");

use lib\nuget\NugetFileParser;
use lib\utils\HttpUtils;
use lib\OminousFactory;

/**
 * UI-facing packages API. Reads/writes the nuget package store (PhpNuget's
 * lib\nuget, MySQL backend) through the bridge and returns it in the uifw grid
 * envelope {items, labels, permissions}. The nuget OData protocol stays in src/api/**.
 */
class PackagesApi extends BaseApis
{
    var Permissions $permissions;

    /** Editable metadata fields exposed by the detail page. */
    private $editable = [
        'Title', 'Author', 'Summary', 'Description', 'Copyright', 'IconUrl',
        'ProjectUrl', 'LicenseUrl', 'ReleaseNotes', 'Tags', 'Listed', 'RequireLicenseAcceptance',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->auth->userMustBeLoggedIn();
        $this->permissions = $this->auth->loadPermissions('packages');
    }

    private function envelope($items)
    {
        return [
            'items'       => $items,
            'labels'      => [],
            'permissions' => $this->permissions->value(),
        ];
    }

    /** GET /api/packages.php — latest version of every listed package. */
    public function apiCallGet()
    {
        $this->permissions->canReadThrow();
        $this->sender->sendSuccessResponse($this->envelope($this->loadPackages(new SearchQuery())));
    }

    /** GET /api/packages.php?action=detail&id=&version= — full metadata + versions. */
    public function apiCallGetDetail()
    {
        $this->permissions->canReadThrow();
        $id      = $this->getParamOrDefault('id');
        $version = $this->getParamOrDefault('version');

        $pkg = nugetPackages()->getByKey($id, $version);
        if ($pkg === null) {
            // fall back to the latest version when only an Id is supplied
            $all = nugetPackageVersions($id);
            $pkg = count($all) ? $all[0] : null;
        }
        if ($pkg === null) {
            $this->sender->sendErrorResponse("ERROR_NOT_FOUND", 404);
            return;
        }

        $versions = [];
        foreach (nugetPackageVersions($id) as $v) {
            $versions[] = [
                'Id'                   => $v->Id,
                'Version'              => $v->Version,
                'Published'            => isset($v->Created) ? $v->Created : '',
                'VersionDownloadCount' => $v->VersionDownloadCount,
            ];
        }

        $this->sender->sendSuccessResponse([
            'item'        => $this->toDetail($pkg),
            'versions'    => $versions,
            'feed'        => nugetSiteRoot(),
            'permissions' => $this->permissions->value(),
        ]);
    }

    /** POST /api/packages.php?action=search — grid search. */
    public function apiCallPostSearch()
    {
        $this->permissions->canReadThrow();
        $sq = $this->getJsonPostData('SearchQuery');
        $this->sender->sendSuccessResponse($this->envelope($this->loadPackages($sq)));
    }

    /** POST /api/packages.php?action=save — persist edited metadata. */
    public function apiCallPostSave()
    {
        $this->permissions->canCreateThrow();
        $data = $this->getJsonPostData();

        $pkg = nugetPackages()->getByKey($data['Id'] ?? null, $data['Version'] ?? null);
        if ($pkg === null) {
            $this->sender->sendErrorResponse("ERROR_NOT_FOUND", 404);
            return;
        }

        foreach ($this->editable as $field) {
            if (!array_key_exists($field, $data)) {
                continue;
            }
            if ($field === 'Author') {
                $pkg->Author = is_array($data[$field])
                    ? $data[$field]
                    : array_map('trim', explode(',', (string)$data[$field]));
            } elseif ($field === 'Listed' || $field === 'RequireLicenseAcceptance') {
                $pkg->$field = filter_var($data[$field], FILTER_VALIDATE_BOOLEAN);
            } else {
                $pkg->$field = $data[$field];
            }
        }

        nugetPackageSave($pkg, false);
        $this->sender->sendSuccessResponse(['item' => $this->toDetail($pkg)]);
    }

    /** POST /api/packages.php?action=delete&id=&version= — remove a version. */
    public function apiCallPostDelete()
    {
        $this->permissions->canDeleteThrow();
        $id      = $this->getParamOrDefault('id');
        $version = $this->getParamOrDefault('version');
        nugetPackageDelete($id, $version);
        $this->sender->sendSuccessResponse();
    }

    /** POST /api/packages.php?action=pull — admin: import a package from an external feed. */
    public function apiCallPostPull()
    {
        $this->permissions->canCreateThrow();
        $data    = $this->getJsonPostData();
        $url     = (string)($data['Url'] ?? '');
        $id      = (string)($data['Id'] ?? '');
        $version = (string)($data['Version'] ?? '');
        if ($url === '' || $id === '' || $version === '') {
            $this->sender->sendErrorResponse("ERROR_GENERIC", 400);
            return;
        }

        $url = str_replace(['@ID', '@VERSION'], [$id, $version], $url);
        $properties = OminousFactory::getObject('properties');
        $content = HttpUtils::download($url);
        $tmp = tempnam(sys_get_temp_dir(), 'pull');
        file_put_contents($tmp, $content);

        $parser  = new NugetFileParser($properties);
        $package = $parser->loadNupkg($tmp);
        $package->UserId = $this->auth->getUser()['id'];

        $root = rtrim(nugetPackagesRoot(), '/');
        if (!is_dir($root)) {
            @mkdir($root, 0775, true);
        }
        rename($tmp, $root . '/' . $package->Id . '.' . $package->Version . '.nupkg');

        nugetPackageSave($package, true);
        $this->sender->sendSuccessResponse(['item' => $this->toDetail($package)]);
    }

    /** POST /api/packages.php?action=refresh — admin: rescan the packages directory. */
    public function apiCallPostRefresh()
    {
        $this->permissions->canCreateThrow();
        $properties = OminousFactory::getObject('properties');
        $parser = new NugetFileParser($properties);
        $root   = rtrim(nugetPackagesRoot(), '/');
        $count  = 0;
        foreach (glob($root . '/*.nupkg') ?: [] as $path) {
            try {
                $package = $parser->loadNupkg($path);
                $package->UserId = $this->auth->getUser()['id'];
                nugetPackageSave($package, true);
                $count++;
            } catch (Exception $e) {
                $this->log->error("refresh failed for $path", $e);
            }
        }
        $this->sender->sendSuccessResponse(['refreshed' => $count]);
    }

    /**
     * Translate the uifw SearchQuery into a nuget query string and run it.
     * @param SearchQuery $sq
     * @return array grid rows
     */
    private function loadPackages($sq)
    {
        $clauses = ["(IsLatestVersion eq true)"];

        $term = null;
        if (is_array($sq->searchTerms) && !empty($sq->searchTerms['search'])) {
            // the nuget grammar delimits string literals with single quotes
            $term = str_replace("'", "", (string)$sq->searchTerms['search']);
        }
        if ($term !== null && $term !== "") {
            $clauses[] = "(substringof('$term',Title) or substringof('$term',Id) or substringof('$term',Description))";
        }

        $query = implode(" and ", $clauses) . " orderBy asc Id";

        $from  = max(0, (int)$sq->from);
        $count = (int)$sq->count;
        if ($count <= 0) {
            $count = 50;
        }

        $rows = nugetPackages()->query($query, $count, $from);
        return $this->toGridRows($rows);
    }

    /** @param \lib\nuget\models\NugetPackage[] $rows */
    private function toGridRows($rows)
    {
        $items = [];
        foreach ($rows as $p) {
            $items[] = [
                'id'            => $p->Id,
                'Id'            => $p->Id,
                'Version'       => $p->Version,
                'Title'         => $p->Title ?: $p->Id,
                'Description'   => $p->Description,
                'DownloadCount' => $p->DownloadCount,
                'Tags'          => $p->Tags,
            ];
        }
        return $items;
    }

    /** Flatten a NugetPackage into the editable detail shape consumed by the form. */
    private function toDetail($p)
    {
        return [
            'Id'                       => $p->Id,
            'Version'                  => $p->Version,
            'Title'                    => $p->Title,
            'Author'                   => is_array($p->Author) ? implode(', ', $p->Author) : $p->Author,
            'Summary'                  => $p->Summary,
            'Description'              => $p->Description,
            'Copyright'                => $p->Copyright,
            'IconUrl'                  => $p->IconUrl,
            'ProjectUrl'               => $p->ProjectUrl,
            'LicenseUrl'               => $p->LicenseUrl,
            'ReleaseNotes'             => $p->ReleaseNotes,
            'Tags'                     => $p->Tags,
            'Listed'                   => (bool)$p->Listed,
            'IsPreRelease'             => (bool)$p->IsPreRelease,
            'RequireLicenseAcceptance' => (bool)$p->RequireLicenseAcceptance,
            'DownloadCount'            => $p->DownloadCount,
            'VersionDownloadCount'     => $p->VersionDownloadCount,
            'Published'                => isset($p->Created) ? $p->Created : '',
        ];
    }
}

$api = new PackagesApi();
$api->handle();
