<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar\Instance;

class MySQLOptimizeIndex extends MySQLOptimize
{
    public function executeTablesOptimize()
    {
        if ($this->isMySQL()) {
            $this->writeln('Running optimize on all database tables, with index dropping and recreation');
            $db = \DBManagerFactory::getInstance();
            $totals = ['initial' => 0, 'final' => 0];
            $results = [];
            $processedTables = [];

            // modules
            $fullModuleList = array_merge($GLOBALS['beanList'], $GLOBALS['app_list_strings']['moduleList']);
            asort($fullModuleList);
            $queryDataSizeEstimation = 'select ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size from information_schema.TABLES where TABLE_NAME = ?';
            foreach($fullModuleList as $module => $label) {
                $bean = \BeanFactory::newBean($module);
                $table = $bean->table_name;
                // if the table exists
                if(!empty($table) && $db->tableExists($table)) {
                    if (!isset($processedTables[$table])) {
                        $processedTables[$table] = '';
                    }
                    // some tables are processed multiple times as they are considered storage for multiple beans, and I have to keep it that way or the indices won't be rebuilt properly
                    if (!isset($results[$table]['initial'])) {
                        // store initial size
                        $stmt = $db->getConnection()->executeQuery($queryDataSizeEstimation, [$table]);
                        if ($row = $stmt->fetch()) {
                            $results[$table]['initial'] = $row['size'];
                            $totals['initial'] += $row['size'];
                        }
                    }
                    // get current indices
                    $indices = $db->get_indices($table);
                    if (!empty($indices)) {
                        $indicesToRemove = [];
                        foreach ($indices as $idx) {
                            if ($idx['type'] !== 'primary') {
                                $indicesToRemove[] = $idx;
                            }
                        }
                        // drop all non-primary indices
                        $queries = $db->dropIndexes($table, $indicesToRemove);
                        $processedTables[$table] .= $queries;
                        // running optimize table
                        $query = 'OPTIMIZE TABLE ' . $table;
                        $this->write('Running query ' . $query . '... ');
                        $stmt = $db->getConnection()->executeQuery($query);
                        $this->write('done. ');
                        $processedTables[$table] .= PHP_EOL . $query . PHP_EOL;
                        // repair table
                        $queries = $db->repairTable($bean);        
                        $processedTables[$table] .= $queries;
                    }
                    // check final size
                    $stmt = $db->getConnection()->executeQuery($queryDataSizeEstimation, [$table]);
                    if ($row = $stmt->fetch()) {
                        $results[$table]['final'] = $row['size'];
                        $totals['final'] += $row['size'];
                    }
                    $this->writeln('Initial est. size: ' . $results[$table]['initial'] . ' MB. Current est size: ' . $results[$table]['final'] . ' MB');
                }
            }
            $this->writeln(PHP_EOL . 'Total initial est. size was: ' . $totals['initial'] . ' MB, total current est. size is: ' . $totals['final'] . ' MB');
        } else {
           $this->writeln('The database in use by this Sugar installation is not MySQL, aborting...'); 
        }
    }
}
