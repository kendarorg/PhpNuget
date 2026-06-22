<?php
require_once("../config.inc");
require_once(__DIR__ . "/../lib/nugetbridge.php");

/**
 * UI-facing packages API. Reads the nuget package store (PhpNuget's lib\nuget,
 * MySQL backend) through the bridge and returns it in the uifw grid envelope
 * {items, labels, permissions}. The nuget OData protocol stays in src/api/**.
 */
class PackagesApi extends BaseApis
{
    var Permissions $permissions;

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

    /** POST /api/packages.php?action=search — grid search. */
    public function apiCallPostSearch()
    {
        $this->permissions->canReadThrow();
        $sq = $this->getJsonPostData('SearchQuery');
        $this->sender->sendSuccessResponse($this->envelope($this->loadPackages($sq)));
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
}

$api = new PackagesApi();
$api->handle();
