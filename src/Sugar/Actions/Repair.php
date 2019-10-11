<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Actions;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class Repair extends Sugar\BaseAction
{
    protected function simpleRepair()
    {
        require_once('modules/Administration/QuickRepairAndRebuild.php');

        // repair
        $repair = new \RepairAndClear();
        $repair->repairAndClearAll(['clearAll'], [$mod_strings['LBL_ALL_MODULES']], true, false, '');
    }

    public function executeSimpleRepair()
    {
        $this->writeln('Executing simple repair...');
        $this->removeTeamFiles();
        $this->writeln('Clearing cache...');
        Instance::clearCache();
        Instance::buildAutoloaderCache();
        $this->simpleRepair();
        Instance::buildAutoloaderCache();
        $this->removeJsAndLanguages();
        $this->writeln('Executing basic instance warm-up...');
        Instance::basicWarmUp();
    }

    protected function removeJsAndLanguages()
    {
        // remove some stuff
        \LanguageManager::removeJSLanguageFiles();
        \LanguageManager::clearLanguageCache();
    }

    protected function removeTeamFiles()
    {
        // remove team cache files
        $files_to_remove = [
            'cache/modules/Teams/TeamSetMD5Cache.php',
            'cache/modules/Teams/TeamSetCache.php'
        ];
        foreach ($files_to_remove as $file) {
            $file = \SugarAutoloader::normalizeFilePath($file);
            if (\SugarAutoloader::fileExists($file)) {
                \SugarAutoloader::unlink($file);
            }
        }
    }
}
