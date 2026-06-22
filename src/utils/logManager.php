<?php
class LogLevel {
    const TRACE   = 2;
    const DEBUG   = 4;
    const INFO    = 8;
    const WARNING = 16;
    const ERROR  = 32;

    public static $labels = [
        self::TRACE   => "TRACE",
        self::DEBUG   => "DEBUG",
        self::INFO    => "INFO",
        self::WARNING => "WARNING",
        self::ERROR  => "ERROR",
    ];

    /**
     * ANSI Escape Codes for Terminal Colors
     */
    public static $colors = [
        self::TRACE   => "\033[0m",    // White/Default
        self::DEBUG   => "\033[0m",    // White/Default
        self::INFO    => "\033[32m",   // Green
        self::WARNING => "\033[33m",   // Yellow
        self::ERROR  => "\033[31m",   // Red
    ];

    const RESET = "\033[0m"; // Resets color back to normal
}

class Logger {
    private $name;
    private $level = null;
    private $supportColor =false;

    public function __construct($name) {
        $this->name = $name;
        $this->supportColor=$this->supportsColors();
    }

    public function setLevel($level) {
        $this->level = $level;
    }

    private function getEffectiveLevel() {
        if ($this->level !== null) return $this->level;
        return LogManager::getParentLevel($this->name);
    }

    public function log($level, $baseMassage, ...$args) {
        if ($level < $this->getEffectiveLevel()) {
            return;
        }
        $now = new \DateTime();
        $timestamp = $now->format('Y-m-d H:i:s.v');
        $message = $baseMassage;
        $throwable = null;
        if(count($args)>0){
            if ($args[count($args)-1] instanceof Throwable) {
                $throwable = $args[count($args)-1];
                array_pop($args);
            }
            $message = sprintf($baseMassage, ...$args);
            if($throwable != null) {
                $exceptionStr = sprintf(
                    "%s%s%sStack Trace:%s%s",
                    PHP_EOL,
                    $throwable->getMessage(),
                    PHP_EOL,
                    PHP_EOL,
                    $throwable->getTraceAsString()
                );
                $message .= $exceptionStr;
            }
        }

        // Get the color and label
        $color = LogLevel::$colors[$level] ?? LogLevel::RESET;
        $label = LogLevel::$labels[$level] ?? "LOG";
        $reset = LogLevel::RESET;
        if(!$this->supportColor) {
            $reset = "";
            $color = "";
        }

        // Format with colors: [Timestamp] [COLOR][LEVEL][RESET] [Name] Message
        $output = sprintf("%s[%s] [%-7s] [%s] %s%s%s",
            $color,
            $timestamp,
            $label,
            $this->name,
            $message,
            $reset,
            PHP_EOL
        );
        if($level == LogLevel::ERROR) {
            file_put_contents('php://stderr', $output);
        }else {
            file_put_contents('php://stdout', $output);
        }
    }

    function supportsColors(): bool
    {
        // If running in CLI and STDOUT is a TTY
        if (PHP_SAPI !== 'cli') {
            return false;
        }

        if (function_exists('stream_isatty')) {
            return stream_isatty(STDOUT);
        }

        if (function_exists('posix_isatty')) {
            return posix_isatty(STDOUT);
        }

        return false;
    }

    public function debug($m, ...$args) {
        $this->log(LogLevel::DEBUG, $m, ...$args); }
    public function info($m, ...$args)  {
        $this->log(LogLevel::INFO, $m, ...$args); }
    public function warn($m, ...$args)  {
        $this->log(LogLevel::WARNING, $m, ...$args); }
    public function error($m, ...$args) {
        $this->log(LogLevel::ERROR, $m, ...$args); }
    public function trace($m, ...$args) {
        $this->log(LogLevel::TRACE, $m, ...$args); }
}

class LogManager {
    private static $loggers = [];
    private static $rootLevel = LogLevel::DEBUG;

    public static function setLoggerLevel($loggerName,$level) {
        self::getLogger($loggerName)->setLevel($level);
    }
    public static function getLogger($name) {
        if (!isset(self::$loggers[$name])) {
            self::$loggers[$name] = new Logger($name);
        }
        return self::$loggers[$name];
    }

    public static function setRootLevel($level) {
        self::$rootLevel = $level;
    }

    /**
     * Logic to find the closest configured parent level
     */
    public static function getParentLevel($name) {
        $parts = explode('.', $name);

        // Traverse upwards: com.app.service -> com.app -> com
        while (array_pop($parts)) {
            $parentName = implode('.', $parts);
            if (isset(self::$loggers[$parentName]) && self::$loggers[$parentName]->getEffectiveLevel() !== null) {
                return self::$loggers[$parentName]->getEffectiveLevel();
            }
        }

        return self::$rootLevel;
    }
}