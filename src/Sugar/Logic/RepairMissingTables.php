<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar;

class RepairMissingTables extends Sugar\BaseLogic
{
    protected function retrieveAndRequireAllDictionaries() : array
    {
        $this->writeln('Retrieving all system\'s SQL tables dictionaries');
        // retrieve all dictionaries for later use
        $dictionaries = array_merge($this->findFiles('metadata', ['/*.php/']), $this->findFiles('modules', ['/vardefs.php/']));
        if (!empty($dictionaries)) {
            foreach ($dictionaries as $dictionaryFile) {
                require($dictionaryFile);
            }
        }
        return $dictionary;
    }

    public function performInitialRepair()
    {
        $this->writeln('Performing initial SQL tables repair');
        $db = \DBManagerFactory::getInstance();

        $retrievedDictionary = $this->retrieveAndRequireAllDictionaries();
        if (!empty($retrievedDictionary)) {
            foreach ($retrievedDictionary as $key => $dictionaryContent) {
                if (!empty($dictionaryContent['table']) && !$db->tableExists($dictionaryContent['table']) && !empty($dictionaryContent['fields']) && !empty($dictionaryContent['indices'])) {
                    $this->write('Repairing SQL table ' . $dictionaryContent['table'] . '... ');
                    $db->repairTableParams($dictionaryContent['table'], $dictionaryContent['fields'], $dictionaryContent['indices']);
                    $this->writeln('done.');
                }
            }
        }

        // now all the modules
        $fullModuleList = $this->getFullModuleList();
        foreach ($fullModuleList as $module => $label) {
            $bean = \BeanFactory::newBean($module);
            $table = $bean->getTableName();
            if (!empty($table) && !$db->tableExists($table)) {
                // execute creation
                $this->write('Detected missing SQL table. Creating ' . $table . '... ');
                $db->createTable($bean);
                $this->writeln('done.');
            }
        }
    }

    public function performFinalRepair()
    {
        $this->writeln('Performing final SQL tables repair');
        include 'include/modules.php';
        $rac = new \RepairAndClear();
        $rac->execute = true;
        $rac->clearVardefs();
        $rac->rebuildExtensions();
        $rac->clearExternalAPICache();
        $rac->setStatementObserver(function (?string $statement) : void {
            $this->writeln('Running the following SQL: ' . $statement);
        });
        $this->writeln('Performing a full system repair');
        $rac->repairDatabase();
        $_REQUEST['silent'] = true;
        include('modules/Administration/RebuildRelationship.php');
        // repair
        $repair = new Sugar\Logic\Repair();
        $repair->setLogger($this->logger);
        $repair->executeSimpleRepair();
        $this->writeln('System repair completed');
    }
}
