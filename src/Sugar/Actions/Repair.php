<?php

namespace Toothpaste\Sugar\Actions;
use Toothpaste\Sugar\Instance;

class Repair
{
    protected static function simpleRepair()
    {
        require_once('modules/Administration/QuickRepairAndRebuild.php');

        // repair
        $repair = new \RepairAndClear();
        $repair->repairAndClearAll(array('clearAll'), array($mod_strings['LBL_ALL_MODULES']), true, false, '');
    }

    public static function executeSimpleRepair()
    {
        echo 'Executing simple repair...' . PHP_EOL;
        self::removeTeamFiles();
        Instance::clearCache();
        Instance::buildAutoloaderCache();
        self::simpleRepair();
        Instance::buildAutoloaderCache();
        self::removeJsAndLanguages();
        Instance::basicWarmUp();
    }

    protected static function removeJsAndLanguages()
    {
        // remove some stuff
        \LanguageManager::removeJSLanguageFiles();
        \LanguageManager::clearLanguageCache();
    }

    protected static function removeTeamFiles()
    {
        // remove team cache files
        $files_to_remove = array(
            'cache/modules/Teams/TeamSetMD5Cache.php',
            'cache/modules/Teams/TeamSetCache.php'
        );
        foreach ($files_to_remove as $file) {
            $file = \SugarAutoloader::normalizeFilePath($file);
            if (\SugarAutoloader::fileExists($file)) {
                \SugarAutoloader::unlink($file);
            }
        }
    }
}
