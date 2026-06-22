<?php

class Translator{
    var $translations = array();
    var $log;

    public function __construct()
    {
        $this->log = LogManager::getLogger("Translator");
    }

    function loadMapFromFile($filename) {
        $map = [];

        $lines = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = ltrim($line);
            if(str_starts_with($line, "#")) continue;
            [$key, $value] = explode('=', $line, 2);
            $key = trim(strtoupper($key));
            $map[$key] = ltrim($value);
        }

        return $map;
    }
    public function translateHt(...$args){
        $result = $this->translate(...$args);
        return htmlspecialchars($result, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public function translate(...$args){
        $this->loadTranslationsIfMissing();
        if(sizeof($args)==0) {
            try{
                throw new Exception("Missing parameters");
            }catch (Exception $e){
                $this->log->warn("Called with no paramers");
                return 'NOARGS';
            }
        }
        $messageIdIndex = strtoupper($args[0]);
        if($messageIdIndex == 'CURRENT_COMPANY_NAME') {
            return GlobalRegistry::get("CURRENT_COMPANY_NAME");
        }
            if (isset($this->translations[$messageIdIndex])) {
                $result = $this->translations[$messageIdIndex];
                for ($i = 1; $i < count($args); $i++) {
                    if(!array_key_exists($i,$args)) {
                        $this->log->error("Error translation ".join(",",$args));
                        throw new Exception("Error translation ".join(",",$args));
                    }
                    $arg = $args[$i];
                    $newArg = null;
                    if ($arg == null) {
                        $newArg = "N/A";
                    } else {
                        if (is_array($arg)) {
                            $newArg = $this->translate(...$arg);
                        } else {
                            $newArg = $this->translate($arg);
                        }

                    }
                    $result = preg_replace("/:\?:/", $newArg, $result, 1);
                }
                return preg_replace("/:\?:/", "", $result);
            } else {
                //$this->log->warn("Missing message id: $messageIdIndex");
            }
        return join(" ",$args);
    }

    public function getAll(){

        $this->loadTranslationsIfMissing();
        return $this->translations;
    }

    /**
     * @return void
     */
    public function loadTranslationsIfMissing(): void
    {
        if (count($this->translations) == 0) {
            try {
                $lang = GlobalRegistry::get('DEFAULT_LANGUAGE');
                $file = GlobalRegistry::get("TRANSLATIONS_PATH") . "/" . $lang . ".ini";
                $this->translations = $this->loadMapFromFile($file);
                unset($this->translations['DB_HOST']);
                unset($this->translations['DB_NAME']);
                unset($this->translations['DB_USER']);
                unset($this->translations['DB_PASS']);
                unset($this->translations['DB_CHARSET']);
                unset($this->translations['NOREPLY_MAIL']);
                unset($this->translations['NOREPLY_USER']);
                unset($this->translations['NOREPLY_PASSWORD']);
                unset($this->translations['NOREPLY_SMTP']);
                unset($this->translations['NOREPLY_SMTP_PORT']);
            } catch (Exception $e) {
                $this->log->error("Unable to load translation file", $e);
                $this->translations = [
                    'FAILED' => 'FAILED'
                ];
            }
        }
    }
}
