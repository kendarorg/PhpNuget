<?php

class Validator
{
    private $baseApi;
    private $log;
    public function __construct($baseApi)
    {
        $this->log  = LogManager::getLogger("GlobalErrors");
        $this->baseApi = $baseApi;
    }

    function validateRequiredFields(&$data, $requiredFields)
    {
        $errors = [];

        if (is_string($requiredFields)) {
            $this->validateRequiredFields($data, [$requiredFields]);
            return;
        }

        foreach ($requiredFields as $field) {

            if (is_string($field)) {
                $optional = strpos($field, '*') === 0;
                if ($optional) {
                    $field = substr($field, 1);
                }
                if (!isset($data[$field]) && !$optional) {
                    if (is_string($data[$field]) && trim($data[$field]) === '') {
                        $errors[$field] = "ERROR_FIELD_REQUIRED|" . $field . "|";
                    } else if (is_array($data[$field]) && sizeof($data[$field]) === 0) {
                        $errors[$field] = "ERROR_FIELD_REQUIRED|" . $field . "|";
                    }
                }
            } else if (is_array($field)) {
                $realField = $field[0];
                $optional = strpos($realField, '*') === 0;
                if ($optional) {
                    $realField = substr($realField, 1);
                }
                $allowedValues = [];
                for ($i = 1; $i < sizeof($field); $i++) {
                    $allowedValues[] = $field[$i];
                }
                if (!in_array($data[$realField], $allowedValues) && !$optional) {
                    $errors[$realField] = "ERROR_FIELD_ENUM_REQUIRED|" . $realField . "|" . join(",", $allowedValues);
                }
            }
        }
        if (sizeof($errors) > 0) {
            $this->sendValidationErrorResponse($errors);
        }

        return $errors;
    }

    function validateDates($data, ...$fields)
    {

        $format = 'Y-m-d';
        $errors = [];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $d = DateTime::createFromFormat($format, $data[$field]);
                if (!$d && $d->format($format) === $data[$field]) {
                    $errors[$field] = "ERROR_DATE_INVALID|" . $field . "|" . $format;
                }
            }
        }

        if (sizeof($errors) > 0) {
            $this->sendValidationErrorResponse($errors);
        }
    }


    function validateNumeric($data, ...$fields)
    {

        $errors = [];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                if (!is_numeric($data[$field])) {
                    $errors[$field] = "NUMERIC_INVALID|" . $field . "|";
                }
            }
        }

        if (sizeof($errors) > 0) {
            $this->baseApi->sendValidationErrorResponse($errors);
        }
    }

    function sendValidationErrorResponse($errors, $message = 'ERROR_VALIDATION')
    {
        $this->log->error("ERRORS " . json_encode($errors));
        $this->baseApi->sendErrorResponse($message, 422, $errors);
    }
}
