<?php

require_once("../config.inc");

class MaintenanceApi extends BaseApis
{
    var Permissions $permissions;

    public function __construct()
    {
        parent::__construct();
        $this->auth->userMustBeLoggedIn();
        $this->permissions = $this->auth->loadPermissions('maintenance');
    }

    private function db()
    {
        return getDbConnection();
    }

    function apiCallGetUpdate_counters()
    {
        $this->permissions->canCreateThrow();
        $conn = $this->db();
        $queries = [];
        $queries[] = "REPLACE INTO counters (table_name, counter) VALUES ('USERS', (SELECT COALESCE(MAX(id)+1,0) FROM users))";
        $queries[] = "REPLACE INTO counters (table_name, counter) VALUES ('TIME_TRACKING', (SELECT COALESCE(MAX(id)+1,0) FROM time_tracking))";
        $queries[] = "REPLACE INTO counters (table_name, counter) VALUES ('TIME_SHEET_CONFIRMATION', (SELECT COALESCE(MAX(id)+1,0) FROM time_sheet_confirmations))";
        $queries[] = "REPLACE INTO counters (table_name, counter) VALUES ('PROTOCOLS', (SELECT COALESCE(MAX(id)+1,0) FROM protocols))";
        $queries[] = "REPLACE INTO counters (table_name, counter) VALUES ('CONTACTS', (SELECT COALESCE(MAX(id)+1,0) FROM contacts))";
        $queries[] = "REPLACE INTO counters (table_name, counter) VALUES ('JOB_INVOICES', (SELECT COALESCE(MAX(id)+1,0) FROM job_invoices))";
        $queries[] = "REPLACE INTO counters (table_name, counter) VALUES ('JOBS', (SELECT COALESCE(MAX(id)+1,0) FROM jobs))";
        $queries[] = "REPLACE INTO counters (table_name, counter) VALUES ('JOB_EXTRA_EXPENSES', (SELECT COALESCE(MAX(id)+1,0) FROM job_extra_expenses))";
        $queries[] = "SET SQL_MODE='ALLOW_INVALID_DATES';UPDATE jobs set startDate = null where startDate='0000-00-00';";
        $queries[] = "SET SQL_MODE='ALLOW_INVALID_DATES';UPDATE jobs set endDate = null where endDate='0000-00-00';";
        $queries[] = "SET SQL_MODE='ALLOW_INVALID_DATES';UPDATE protocols set sendingDate = null where sendingDate='0000-00-00';";
        $queries[] = "SET SQL_MODE='ALLOW_INVALID_DATES';UPDATE job_invoices set date = null where date='0000-00-00';";
        $queries[] = "INSERT INTO counters (`table_name`, `counter`)
(
SELECT CONCAT('protocols.',`YEAR`) AS table_name,
       MAX_PROTOCOL AS counter
FROM (SELECT SUBSTRING_INDEX(`number`, '/', 1)                         as YEAR,
             MAX(CAST(SUBSTRING_INDEX(`number`, '/', -1) AS UNSIGNED)) as MAX_PROTOCOL
      FROM protocols
      GROUP BY `YEAR`) AS protocols
WHERE YEAR IS NOT NULL
)
ON DUPLICATE KEY UPDATE
    `counter` = VALUES(counter);";

        $errors = [];
        foreach ($queries as $q) {
            try {
                $conn->exec($q);
            } catch (Exception $e) {
                $errors[] = $e->getMessage();
            }
        }
        $this->sender->sendSuccessResponse([
            'updated' => count($queries) - count($errors),
            'errors' => $errors,
        ]);
    }

    function apiCallGetJobs_to_workers()
    {
        $this->permissions->canCreateThrow();
        $conn = $this->db();
        $runForReal = ($this->getParamOrDefault('simulate') === 'false');

        $users = $this->audit->fetchAll(
            "MAINTENANCE", "JOB_TO_WORKERS", $conn,
            "SELECT id, tariffaOraria, username FROM users"
        );
        $usMap = [];
        $usnMap = [];
        foreach ($users as $u) {
            $key = $u['id'] . '';
            $usMap[$key] = (float)($u['tariffaOraria'] ?? 0);
            $usnMap[$key] = $u['username'];
        }

        $workersOnJob = [];
        $timeSheet = $this->audit->fetchAll(
            "MAINTENANCE", "JOB_TO_WORKERS", $conn,
            "SELECT commessa, user_id FROM time_tracking WHERE kind<>'Assenza' GROUP BY commessa, user_id"
        );
        $tsMap = [];
        foreach ($timeSheet as $ts) {
            $key = $ts['commessa'] . '-' . $ts['user_id'];
            $tsMap[$key] = $ts;
            if (!isset($usMap[$ts['user_id']])) {
                $tsMap[$key]['tariffa'] = 0.0;
                continue;
            }
            if (!isset($workersOnJob[$ts['commessa']])) {
                $workersOnJob[$ts['commessa']] = [];
            }
            $workersOnJob[$ts['commessa']][$ts['user_id']] = "USER";
            $tsMap[$key]['tariffa'] = $usMap[$ts['user_id'] . ''];
        }

        $possDir = $this->audit->fetchAll(
            "MAINTENANCE", "JOB_TO_WORKERS", $conn,
            "SELECT id, role FROM users WHERE role='Socio'"
        );
        $dtMap = [];
        foreach ($possDir as $d) {
            $dtMap[$d['id']] = "DIR";
        }

        $workersPresent = $this->audit->fetchAll(
            "MAINTENANCE", "JOB_TO_WORKERS", $conn,
            "SELECT job_id, user_id, type FROM job_workers GROUP BY job_id, user_id"
        );
        $wpMap = [];
        foreach ($workersPresent as $w) {
            $key = $w['job_id'] . '-' . $w['user_id'];
            $wpMap[$key] = $w;
        }

        $insertSql = "INSERT INTO job_workers (job_id, user_id, type, customerHourlyRate) VALUES (?, ?, ?, ?)";
        $result = [];
        foreach ($tsMap as $key => $ts) {
            if (isset($wpMap[$key])) continue;
            if (!isset($ts['user_id'])) continue;
            if (!isset($ts['commessa'])) continue;

            $type = 'WO';
            if (isset($workersOnJob[$ts['commessa']]) && count($workersOnJob[$ts['commessa']]) == 1) {
                if (isset($dtMap[$ts['user_id']])) {
                    $type = 'TD';
                }
            }
            $result[] = [
                'commessa' => $ts['commessa'],
                'user_id' => $ts['user_id'],
                'username' => $usnMap[$ts['user_id'] . ''] ?? '',
                'tariffa' => $ts['tariffa'],
                'type' => $type,
            ];
            if (!$runForReal) continue;
            $this->audit->execute(
                "MAINTENANCE", "JOB_TO_WORKERS", $conn, $insertSql,
                [$ts['commessa'], $ts['user_id'], $type, $ts['tariffa']]
            );
        }

        $this->sender->sendSuccessResponse(['items' => $result]);
    }

    function apiCallGetShow_possible_errors()
    {
        $this->permissions->canReadThrow();
        $conn = $this->db();

        $sql = "SELECT date,
       user_id,
       username,
       commessa,
       max(tariffaOraria)      as tariffaOraria,
       min(tariffaCliente)     AS tariffaCliente,
       max(tariffaCliente)     AS tariffaClienteMax,
       max(customerHourlyRate) AS customerHourlyRate,
       sum(oreLavorate)        AS oreLavorate,
       COUNT(*)                AS total_records,
       max(acorpo)             as acorpo
FROM (SELECT DATE_SUB(time_tracking.date, INTERVAL WEEKDAY(time_tracking.date) DAY) AS date,
             time_tracking.user_id,
             time_tracking.commessa,
             COALESCE(users.tariffaOraria, 0) as tariffaOraria,
             COALESCE(time_tracking.tariffaCliente, 0) as tariffaCliente,
             COALESCE(time_tracking.oreLavorate, 0) + (COALESCE(time_tracking.minutiLavorati, 0)/60) as oreLavorate,
             COALESCE(job_workers.customerHourlyRate, 0) as customerHourlyRate,
             users.username as username,
             jobs.acorpo as acorpo
      FROM time_tracking
               LEFT JOIN job_workers
                         ON time_tracking.user_id = job_workers.user_id AND time_tracking.commessa = job_workers.job_id
               LEFT JOIN users
                         ON time_tracking.user_id = users.id
               LEFT JOIN jobs
                         ON time_tracking.commessa = jobs.id
      WHERE time_tracking.kind <> 'Assenza'
        AND time_tracking.commessa <> '1014'
        AND time_tracking.commessa <> '1049'
        AND time_tracking.commessa <> '1006'
        AND time_tracking.user_id <> 77
        AND time_tracking.date >
    (SELECT DATE_ADD(MAX(date), INTERVAL (7 - WEEKDAY(MAX(date))) DAY) FROM time_sheet_confirmations WHERE user_id = time_tracking.user_id)) as week_separate
GROUP BY date, user_id, commessa, username
ORDER BY commessa DESC, date, user_id;";

        $result = $this->audit->fetchAll("MAINTENANCE", "POSSIBLE_ERRORS", $conn, $sql);
        $tariffaOrariaZero = [];
        $erroreCommessa = [];

        foreach ($result as $item) {
            if ($item['tariffaOraria'] <= 0.1) {
                $tariffaOrariaZero[$item['username']] = [
                    "errorId" => "tariffaOrariaZero",
                    "error" => "Tariffa oraria zero per utente " . $item['username'] . ". Aggiornare su gestione utenti",
                    "username" => $item['username'],
                    "user_id" => $item['user_id'],
                    "commessa" => "",
                    "data" => $item,
                ];
            }
            if ($item['customerHourlyRate'] != $item['tariffaCliente'] || $item['customerHourlyRate'] != $item['tariffaClienteMax'] ||
                $item['tariffaCliente'] != $item['tariffaClienteMax']) {
                if ($item['customerHourlyRate'] < $item['tariffaOraria'] || $item['tariffaClienteMax'] < $item['tariffaOraria']
                    || $item['tariffaCliente'] < $item['tariffaOraria']) {
                    if (!isset($item['acorpo']) || $item['acorpo'] < 0.1) {
                        $erroreCommessa = $this->tariffaClienteSottoCosto($erroreCommessa, $item);
                    }
                } else {
                    if (!isset($item['acorpo']) || $item['acorpo'] < 0.1) {
                        $erroreCommessa = $this->tariffaClienteNonCorretta($erroreCommessa, $item);
                    }
                }
            }
            if ($item['tariffaCliente'] <= $item['tariffaOraria']) {
                if (!isset($item['acorpo']) || $item['acorpo'] < 0.1) {
                    $erroreCommessa = $this->tariffaClienteSottoCosto($erroreCommessa, $item);
                }
            }
        }

        $users = $this->audit->fetchAll(
            "MAINTENANCE", "POSSIBLE_ERRORS", $conn,
            "SELECT id, username, tariffaOraria FROM users WHERE tariffaOraria<=0.1 AND locked=0"
        );
        foreach ($users as $u) {
            if (!isset($tariffaOrariaZero[$u['username']])) {
                $tariffaOrariaZero[$u['username']] = [
                    "errorId" => "tariffaOrariaZero",
                    "error" => "Tariffa oraria zero per utente " . $u['username'] . ". Aggiornare su gestione utenti",
                    "username" => $u['username'],
                    "user_id" => $u['id'],
                    "commessa" => "",
                    "data" => $u,
                ];
            }
        }

        $this->sender->sendSuccessResponse([
            'tariffaOrariaZero' => $tariffaOrariaZero,
            'erroreCommessa' => $erroreCommessa,
        ]);
    }

    function apiCallGetConfirm_time_sheets()
    {
        $this->permissions->canCreateThrow();
        $conn = $this->db();
        $toDate = $this->getParamOrDefault('to_date');
        if (!$toDate) {
            $this->sender->sendErrorResponse("MISSING_PARAMETER", 400, ['to_date']);
            return;
        }
        try {
            $conn->beginTransaction();
            $sql = "INSERT INTO time_sheet_confirmations (id, `date`, user_id, confirmed)
SELECT * FROM (
SELECT (@max_id := @max_id + 1) AS id,
       week_start,
       user_id,
       1 AS confirmed
FROM (SELECT *
      FROM (SELECT STR_TO_DATE(CONCAT(YEARWEEK(tt.date, 1), ' Monday'), '%X%V %W') AS week_start,
                   tt.user_id as user_id
            FROM time_tracking tt
            WHERE tt.user_id IS NOT NULL
              AND tt.date <= ?) as vv
      GROUP BY user_id, week_start) weeks
CROSS JOIN (SELECT @max_id := IFNULL(MAX(id), 0) FROM time_sheet_confirmations) init
) as wheretoinsert
ON DUPLICATE KEY UPDATE confirmed = 1;";

            $stmt = $conn->prepare($sql);
            $stmt->execute([$toDate]);
            $stmt = $conn->prepare("UPDATE counters SET counter = (SELECT MAX(id+0) FROM time_sheet_confirmations) WHERE table_name='TIME_SHEET_CONFIRMATION';");
            $stmt->execute();
            $conn->commit();
            $this->sender->sendSuccessResponse(['result' => 'OK']);
        } catch (Exception $e) {
            $conn->rollBack();
            $this->sender->sendErrorResponse($e->getMessage(), 500);
        }
    }

    function apiCallGetShow_log_data()
    {
        $this->permissions->canReadThrow();
        $conn = $this->db();
        $id = $this->getParamOrDefault('id');
        $logs = $this->audit->fetchAll(
            "MAINTENANCE", "LOG_DATA", $conn,
            "SELECT operations_log.*, users.username FROM operations_log LEFT JOIN users ON users.id=operations_log.user_id WHERE operations_log.id=?",
            $id
        );
        $log = count($logs) > 0 ? $logs[0] : null;
        $this->sender->sendSuccessResponse(['item' => $log]);
    }

    function apiCallPostSearch_logs()
    {
        $this->permissions->canReadThrow();
        $conn = $this->db();
        $data = $this->getJsonPostData('SearchQuery');
        $terms = is_object($data) ? ($data->searchTerms ?? []) : [];

        $queryParams = [];
        $queryValues = [];

        $applyLike = function ($field, $sqlExpr) use (&$queryParams, &$queryValues, $terms) {
            if (!isset($terms[$field])) return;
            $val = trim((string)$terms[$field]);
            if ($val === '') return;
            $parts = explode(',', $val);
            $tmp = [];
            foreach ($parts as $p) {
                $tmp[] = $sqlExpr;
                $queryValues[] = trim($p);
            }
            $queryParams[] = " ( " . implode(" OR ", $tmp) . " ) ";
        };

        $applyLike('type', "type LIKE CONCAT(?, '%')");
        $applyLike('operation', "operation LIKE CONCAT(?, '%')");
        $applyLike('user_id', "user_id = ?");

        if (isset($terms['data']) && trim((string)$terms['data']) !== '') {
            $queryValues[] = trim((string)$terms['data']);
            $queryParams[] = " data LIKE CONCAT('%', ?, '%') ";
        }
        if (isset($terms['operationId']) && trim((string)$terms['operationId']) !== '') {
            $queryValues[] = trim((string)$terms['operationId']);
            $queryParams[] = " operationId = ? ";
        }

        $sql = "SELECT operations_log.*, users.username FROM operations_log LEFT JOIN users ON users.id=operations_log.user_id";
        if (count($queryParams) > 0) {
            $sql .= " WHERE " . implode(' AND ', $queryParams);
        }

        $orderBy = " id DESC ";
        if (is_object($data) && !empty($data->orderBy) && is_array($data->orderBy) && count($data->orderBy) > 0) {
            $first = trim((string)$data->orderBy[0]);
            if ($first !== '' && preg_match('/^[A-Za-z0-9_]+\s+(ASC|DESC)$/i', $first)) {
                $orderBy = $first;
            }
        }
        $sql .= " ORDER BY $orderBy";

        $count = (is_object($data) && isset($data->count) && (int)$data->count > 0) ? (int)$data->count : 21;
        $from = (is_object($data) && isset($data->from) && (int)$data->from > 0) ? (int)$data->from : 0;
        if ($from > 0) {
            $sql .= " LIMIT $count OFFSET $from";
        } else {
            $sql .= " LIMIT $count";
        }

        $logs = $this->audit->fetchAll("MAINTENANCE", "LOG_DATA", $conn, $sql, $queryValues);
        foreach ($logs as &$log) {
            if (isset($log['data']) && strlen($log['data']) > 50) {
                $log['data'] = substr($log['data'], 0, 50) . "...";
            }
        }
        unset($log);

        $this->sender->sendSuccessResponse(['items' => $logs]);
    }

    private function tariffaClienteNonCorretta(array $erroreCommessa, $item): array
    {
        if (!isset($erroreCommessa[$item['commessa']])) $erroreCommessa[$item['commessa']] = ["errors" => []];
        if (!isset($erroreCommessa[$item['commessa']][$item['username']])) $erroreCommessa[$item['commessa']][$item['username']] = [];
        $erroreCommessa[$item['commessa']]['errors']["tariffaClienteNonCorretta"] = true;
        $erroreCommessa[$item['commessa']][$item['username']]['tariffaClienteNonCorretta'] = [
            "error" => "Tariffa cliente non corrispondente per utente " . $item['username'] . " in commessa " . $item['commessa'] . ". Aggiornare su commessa.",
            "username" => $item['username'],
            "user_id" => $item['user_id'],
            "commessa" => $item['commessa'],
            "errorId" => "tariffaClienteNonCorretta",
            "data" => $item,
        ];
        return $erroreCommessa;
    }

    private function tariffaClienteSottoCosto(array $erroreCommessa, $item): array
    {
        if (!isset($erroreCommessa[$item['commessa']])) $erroreCommessa[$item['commessa']] = ["errors" => []];
        if (!isset($erroreCommessa[$item['commessa']][$item['username']])) $erroreCommessa[$item['commessa']][$item['username']] = [];
        $erroreCommessa[$item['commessa']]['errors']["tariffaClienteSottoCosto"] = true;
        $erroreCommessa[$item['commessa']][$item['username']]['tariffaClienteSottoCosto'] = [
            "error" => "Tariffa cliente sotto costo per utente " . $item['username'] . " in commessa " . $item['commessa'] . ". Aggiornare su commessa.",
            "username" => $item['username'],
            "user_id" => $item['user_id'],
            "commessa" => $item['commessa'],
            "errorId" => "tariffaClienteSottoCosto",
            "data" => $item,
        ];
        return $erroreCommessa;
    }
}

$api = new MaintenanceApi();
$api->handle();
