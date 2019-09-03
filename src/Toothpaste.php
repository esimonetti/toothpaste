<?php

// Enrico Simonetti
// enricosimonetti.com
//
// 2019-09-03 on Sugar 9.0.0

namespace Toothpaste;

class Toothpaste
{
    const SW_VERSION = '0.0.1';
    const SW_NAME = 'Toothpaste';

    private static function getSoftwareVersionNumber()
    {
        return self::SW_VERSION;
    }
    
    private static function getSoftwareName()
    {
        return self::SW_NAME;
    }

    public static function getSoftwareInfo()
    {
        return self::getSoftwareName() . ' v' . self::getSoftwareVersionNumber();
    }
}
