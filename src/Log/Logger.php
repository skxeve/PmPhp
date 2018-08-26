<?php
namespace PmPhp\Log;

use PmPhp\AbstractClass;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;

class Logger extends AbstractClass implements LoggerInterface
{
    use LoggerTrait;

    private static $logLevelMap = [
        0 => LogLevel::DEBUG,
        1 => LogLevel::INFO,
        2 => LogLevel::NOTICE,
        3 => LogLevel::WARNING,
        4 => LogLevel::ERROR,
        5 => LogLevel::CRITICAL,
        6 => LogLevel::ALERT,
        7 => LogLevel::EMERGENCY,
    ];

    private static $logLevelSyslogMap = [
        0 => LOG_DEBUG,
        1 => LOG_INFO,
        2 => LOG_NOTICE,
        3 => LOG_WARNING,
        4 => LOG_ERR,
        5 => LOG_CRIT,
        6 => LOG_ALERT,
        7 => LOG_EMERG,
    ];



    private $enableLogLevel = 1;

    protected static $singleton = null;

    protected function __construct()
    {
    }

    public static function get()
    {
        if (!self::$singleton) {
            self::$singleton = new static();
        }
        return self::$singleton;
    }

    public function log($level, $message, array $context = array())
    {
        $levelInt = array_search($level, self::$logLevelMap);
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $file = $this->trimPath($trace[1]['file']);
        $line = $trace[1]['line'];
        syslog(self::$logLevelSyslogMap[$levelInt], vsprintf('[%s] %s:%s %s', [$level, $file, $line, vsprintf($message, $context)]));
    }

    protected function trimPath($file)
    {
        static $rootDir = null;
        if ($rootDir === null || strpos($file, $rootDir) !== 0) {
            $rootDir = dirname(dirname($file));
            if (($pos = strpos($rootDir, 'vendor')) !== false) {
                $rootDir = substr($rootDir, 0, $pos);
            }
        }
        if (strpos($file, $rootDir) === 0) {
            return substr($file, strlen($rootDir) + 1);
        } else {
            return $file;
        }
        
    }
}
