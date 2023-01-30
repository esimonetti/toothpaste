<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste;

class Toothpaste
{
    const SW_VERSION = '0.2.4';
    const SW_NAME = 'Toothpaste';
    const SUPPORTED_OS = array('linux', 'darwin');

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

    public static function isOSSupported()
    {
        return in_array(self::getOS(), self::SUPPORTED_OS);
    }

    public static function registerSupportInfo()
    {
        register_shutdown_function(
            function() {
                print(PHP_EOL . PHP_EOL . 'If you find this software useful, please consider supporting the work that went into it, with a monthly amount' . PHP_EOL .
                    'Please visit the original repo: https://github.com/esimonetti/toothpaste for details' . PHP_EOL .
                    'Thank you!' . PHP_EOL . PHP_EOL
                );
            }
        );
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
