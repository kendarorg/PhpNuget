<?php

require_once("../config.inc");

class FilesApi extends BaseApis
{
    var Permissions $permissions;
    var $labels;

    public function __construct()
    {
        parent::__construct();
        $this->auth->userMustBeLoggedIn();
        $this->permissions = $this->auth->loadPermissions('files');
        $this->labels = [
            "id"          => translate("id"),
            "name"        => translate("NAME"),
            "path"        => translate("PATH"),
            "description" => translate("DESCRIPTION"),
        ];
    }

    private function sanitizePath($path)
    {
        $path = str_replace(["\0", "\x00"], '', $path);
        $path = str_replace(['../', '..' . '\\', '.'], '', $path);
        $path = preg_replace('/\.{2,}/', '.', $path);
        $path = str_replace(['$', '%', '&', '|', ';', '`', '<', '>', '"', "'"], '', $path);
        $path = preg_replace('/[\x00-\x1F\x7F]/', '', $path);
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);
        $path = trim($path, " \t\n\r\0\x0B./");
        if (!empty($path) && $path[0] !== '/') {
            $path = '/' . $path;
        }
        if (strlen($path) > 255) {
            throw new Exception('Path is too long');
        }
        $parts = explode("/", $path);
        if (count($parts) > 3) {
            throw new Exception('Path is too deep');
        }
        return $path;
    }

    private function sanitizeName($name)
    {
        $parts = explode(".", $name);
        $nm    = str_replace("/", "", $this->sanitizePath($parts[count($parts) - 2]));
        $ext   = str_replace("/", "", $this->sanitizePath($parts[count($parts) - 1] ?? ''));
        if (strlen($nm) === 0 || strlen($ext) === 0) {
            throw new Exception('Invalid file name');
        }
        return $nm . "." . $ext;
    }

    private function getBlobPath()
    {
        return GlobalRegistry::get("BLOB_PATH");
    }

    function apiCallPostSearch()
    {
        $this->permissions->canReadThrow();
        $data = $this->getJsonPostData('SearchQuery');
        $path = $data->searchTerms['path'] ?? '/';
        if (!str_starts_with($path, "/")) {
            $path = "/" . $path;
        }
        $db    = getDbConnection();
        $items = $this->audit->fetchAll(
            $this, "SEARCH", $db,
            "SELECT id, name, description, path FROM files WHERE path LIKE CONCAT('/documenti', ?, '%') ORDER BY created_at DESC",
            $path
        );
        for ($i = 0; $i < count($items); $i++) {
            $items[$i]['path'] = substr($items[$i]['path'], strlen("/documenti"));
        }
        $result = [
            'items'       => $items,
            'labels'      => $this->labels,
            'permissions' => $this->permissions->value(),
        ];
        $this->sender->sendSuccessResponse($result);
    }

    function apiCallGetDownload()
    {
        $this->permissions->canReadThrow();
        $fileId = $this->getParamOrDefault("id");
        if (empty($fileId)) {
            $this->sender->sendErrorResponse("FILE_ID_REQUIRED", 400);
            return;
        }
        $db   = getDbConnection();
        $file = $this->audit->fetch(
            $this, "DOWNLOAD", $db,
            "SELECT name, path FROM files WHERE id = ?",
            $fileId
        );
        if (!$file) {
            $this->sender->sendErrorResponse(translate('FILE_NOT_FOUND', $fileId), 404);
            return;
        }
        $fileName = $this->sanitizeName($file['name']);
        $filePath = $this->getBlobPath() . "/" . ltrim($this->sanitizePath($file['path']), '/') . "/" . $fileName;
        if (!file_exists($filePath)) {
            $this->sender->sendErrorResponse(translate('FILE_NOT_FOUND', $filePath), 404);
            return;
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }

    function apiCallPostUpload()
    {
        $this->permissions->canCreateThrow();
        $input = $this->getJsonPostData();
        if (empty($input['content']) || empty($input['name']) || !isset($input['description']) || empty($input['path'])) {
            $this->sender->sendErrorResponse('Missing required fields: content, name, description, path', 400);
            return;
        }
        $this->audit->audit($this, "CREATE", [
            'name'        => $input['name'],
            'description' => $input['description'],
            'path'        => $input['path'],
        ]);
        $fileName    = $this->sanitizeName($input['name']);
        if (isset($input['$newName'])) {
            $fileName = $this->sanitizeName($input['$newName']);
        }
        $storagePath = "/documenti/" . ltrim($this->sanitizePath($input['path']), '/');
        $fileContent = base64_decode($input['content']);
        if ($fileContent === false) {
            $this->audit->auditError($this, "CREATE", 'Invalid file content encoding');
            $this->sender->sendErrorResponse('Invalid file content encoding', 400);
            return;
        }
        $fullDir = $this->getBlobPath() . '/' . ltrim($storagePath, '/');
        if (!is_dir($fullDir) && !mkdir($fullDir, 0755, true)) {
            $this->sender->sendErrorResponse('Failed to create storage directory', 500);
            return;
        }
        if (file_put_contents($fullDir . '/' . $fileName, $fileContent) === false) {
            $this->audit->auditError($this, "CREATE", 'Failed to save file');
            $this->sender->sendErrorResponse('Failed to save file', 500);
            return;
        }
        $db   = getDbConnection();
        $stmt = $db->prepare("INSERT INTO files (name, description, path, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$fileName, $input['description'], $storagePath]);
        $this->sender->sendSuccessResponse(['id' => $db->lastInsertId()]);
    }

    function apiCallDelete()
    {
        $this->permissions->canDeleteThrow();
        $fileId = $this->getParamOrDefault("id");
        if (empty($fileId)) {
            $this->sender->sendErrorResponse('File ID is required', 400);
            return;
        }
        $this->audit->audit($this, "DELETE", $fileId);
        $db   = getDbConnection();
        $file = $this->audit->fetch(
            $this, "DELETE_LOOKUP", $db,
            "SELECT name, path FROM files WHERE id = ?",
            $fileId
        );
        if (!$file) {
            $this->sender->sendErrorResponse('File not found', 404);
            return;
        }
        $fileName = $this->sanitizeName($file['name']);
        $filePath = $this->getBlobPath() . "/" . ltrim($this->sanitizePath($file['path']), '/') . "/" . $fileName;
        $db->prepare("DELETE FROM files WHERE id = ?")->execute([$fileId]);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        $this->sender->sendSuccessResponse();
    }


    function apiCallGet()
    {
        $db   = getDbConnection();
        $functionalitiesList = $this->audit->fetchAll(
            $this, "LIST", $db,
            "SELECT id,name FROM functionalities WHERE functionality=?",
            'documenti'
        );
        $functionalities = [];
        foreach ($functionalitiesList as $functionality) {
            $functionalities[$functionality['id']] = $functionality['name'];
        }

        $files = $this->audit->fetchAll(
            $this, "LIST", $db,
            "SELECT id,name, path,description FROM files WHERE path LIKE '/documenti/%' ORDER BY path ASC, description asc"
        );
        $realItems =[];
        $functionality = null;
        foreach ($files as $file) {
            foreach ($functionalities as $key=>$v) {
                $res = stripos($file['path'], "/documenti/".$key);
                if($res !== false && $res==0){
                    if($functionality!=$key){
                        $realItems[]= "th=".$v;
                        $functionality = $key;
                    }
                    $realItems[]=$file;
                    break;
                }
            }

        }
        $items = [
            'items'=>$realItems,
            'labels'=>$this->labels,
            'permissions'=>$this->permissions->value(),
        ];
        $this->sender->sendSuccessResponse($items);
    }
}

$api = new FilesApi();
$api->handle();
