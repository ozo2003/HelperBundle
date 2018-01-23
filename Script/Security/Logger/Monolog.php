<?php

namespace Sludio\HelperBundle\Script\Security\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Monolog
{
    public static function log($message, array $context = [], $type = 'info', $level = 0)
    {
        self::getType($type);

        try {
            $log = self::registerLog();
            $debug = \debug_backtrace()[$level];
            $details = [
                $debug['file'],
                $debug['line'],
            ];
            $context = !empty($context) ? \array_merge($context, $details) : $details;

            $log->{strtolower($type)}($message, $context);
        } catch (\Exception $exception) {

        }
    }

    private static function getType(&$type)
    {
        if (!\array_key_exists(\strtoupper($type), Logger::getLevels())) {
            $type = Logger::getLevelName(Logger::INFO);
        }
    }

    /**
     * @throws \Exception
     */
    public static function registerLog()
    {
        $log = new Logger('sludio_helper');
        $directory = date('Y-m-j').'_vendor';
        $log->pushHandler(new StreamHandler(sprintf(getcwd().'/../app/logs/vendor/%s.log', $directory)));

        return $log;
    }
}
