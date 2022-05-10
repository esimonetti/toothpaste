<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste;

class Toothpaste
{
    const SW_VERSION = '0.2.2';
    const SW_NAME = 'Toothpaste';

    protected static $startTime;

    public static function getSoftwareVersionNumber()
    {
        return self::SW_VERSION;
    }
    
    public static function getSoftwareName()
    {
        return self::SW_NAME;
    }

    public static function getSoftwareInfo()
    {
        return self::getSoftwareName() . ' v' . self::getSoftwareVersionNumber();
    }

    public static function getOS()
    {
        return strtolower(php_uname('s'));
    }

    public static function isLinux()
    {
        return (self::getOS() === 'linux' || self::getOS() === 'darwin') ? true : false;
    }

    public static function resetStartTime()
    {
        self::$startTime = microtime(true);
        register_shutdown_function(
            function($start) {
                print('Execution completed in ' . sprintf('%0.2f', round((microtime(true) - $start), 2)) . ' seconds.' . PHP_EOL);
            },
            self::$startTime
        );
    }

    public static function getStartTime()
    {
        if (empty(self::$startTime)) {
            self::resetStartTime();
        }

        return self::$startTime;
    }
}
