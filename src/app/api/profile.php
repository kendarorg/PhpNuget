<?php
require_once("../config.inc");
require_once(__DIR__ . "/../lib/nugetbridge.php");

/**
 * Profile / "my packages" API. Everything here is scoped to the logged-in user
 * resolved from the uifw session (never by API key). Surfaces the user's own
 * packages, profile fields, and upload API-key (token) management.
 */
class ProfileApi extends BaseApis
{
    var $model;

    public function __construct()
    {
        parent::__construct();
        $this->auth->userMustBeLoggedIn();
        $this->model = GlobalRegistry::get("UsersModel");
    }

    private function userId()
    {
        return $this->auth->getUser()['id'];
    }

    /** GET /api/profile.php — the current user's editable profile (apiKey masked). */
    public function apiCallGet()
    {
        $data = $this->model->getById($this->userId());
        $this->purgeItem($data, "password", "permissions");
        $apiKey = nugetUserApiKey($this->userId());
        $data['apiKey']       = $this->mask($apiKey);
        $data['apiKeyFull']   = $apiKey;     // shown to the owner so they can configure nuget
        $result = [
            'item'  => $data,
            'feed'  => nugetSiteRoot(),
        ];
        $this->sender->sendSuccessResponse($result);
    }

    /** POST /api/profile.php?action=save — update own name/company/email. */
    public function apiCallPostSave()
    {
        $posted   = $this->getJsonPostData();
        $existing = $this->model->getById($this->userId());
        if (!$existing) {
            $this->sender->sendErrorResponse("ERROR_NOT_FOUND", 404);
            return;
        }
        // only let the user touch their own descriptive fields; keep everything else.
        foreach (['ragioneSociale', 'email', 'telefono'] as $field) {
            if (array_key_exists($field, $posted)) {
                $existing[$field] = $posted[$field];
            }
        }
        unset($existing['password']);
        $existing['id'] = $this->userId();
        $this->model->update($existing);
        $this->sender->sendSuccessResponse();
    }

    /** POST /api/profile.php?action=password — change own password. */
    public function apiCallPostPassword()
    {
        $data = $this->getJsonPostData();
        if (empty($data['password'])) {
            $this->sender->sendErrorResponse("ERROR_GENERIC", 400);
            return;
        }
        $this->model->updatePassword($this->userId(), $data['password']);
        $this->sender->sendSuccessResponse();
    }

    /** POST /api/profile.php?action=search — grid of packages owned by the current user. */
    public function apiCallPostSearch()
    {
        $sq = $this->getJsonPostData('SearchQuery');
        $rows = nugetPackagesByOwner($this->userId(), $sq);
        $items = [];
        foreach ($rows as $p) {
            $items[] = [
                'id'            => $p->Id,
                'Id'            => $p->Id,
                'Version'       => $p->Version,
                'Title'         => $p->Title ?: $p->Id,
                'DownloadCount' => $p->DownloadCount,
                'Tags'          => $p->Tags,
            ];
        }
        $this->sender->sendSuccessResponse(['items' => $items, 'labels' => []]);
    }

    /** POST /api/profile.php?action=regenerateApiKey — issue a fresh upload token. */
    public function apiCallPostRegenerateApiKey()
    {
        $key = nugetUserApiKeyRegenerate($this->userId());
        $this->audit->audit("REGENERATE_APIKEY", "USER", ['user' => $this->userId()]);
        $this->sender->sendSuccessResponse(['apiKey' => $key]);
    }

    private function mask($key)
    {
        if (!$key) {
            return '';
        }
        $len = strlen($key);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return str_repeat('*', $len - 4) . substr($key, -4);
    }
}

$api = new ProfileApi();
$api->handle();
