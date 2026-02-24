<?php
namespace Core;

class Logger
{
    public static function info(string $message): void
    {
        self::writeLog('info', $message);
    }

    public static function warning(string $message): void
    {
        self::writeLog('warning', $message);
    }

    public static function error(string $message): void
    {
        self::writeLog('error', $message);
    }

    private static function writeLog(string $level, string $message): void
    {
        $date = date('Y-m-d');
        $time = date('H:i:s');
        $logDir = BASE_PATH . '/storage/logs';

        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $filePath = "{$logDir}/{$date}.log";
        $logLine = "[{$time}] [{$level}] {$message}\n";

        file_put_contents($filePath, $logLine, FILE_APPEND);
    }
}
