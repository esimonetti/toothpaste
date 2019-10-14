<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Actions;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class MySQLOptimize extends Sugar\MySQLBaseAction
{
    public function executeTablesOptimize()
    {
        if ($this->isMySQL()) {
            $this->writeln('Running optimize on all database tables');
            $db = \DBManagerFactory::getInstance();
            $tables = $db->getTablesArray();
            asort($tables);
            foreach ($tables as $table) {
                // store initial size
                $query = 'select ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size from information_schema.TABLES where TABLE_NAME = ?';
                $stmt = $db->getConnection()->executeQuery($query, [$table]);
                if ($row = $stmt->fetch()) {
                    $results[$table]['initial'] = $row['size'];
                    $totals['initial'] += $row['size'];
                }
                $query = 'OPTIMIZE TABLE ' . $table;
                $this->write('Running query ' . $query . '... ');
                $stmt = $db->getConnection()->executeQuery($query);
                $this->write('done. ');
                // check final size
                $query = 'select ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size from information_schema.TABLES where TABLE_NAME = ?';
                $stmt = $db->getConnection()->executeQuery($query, [$table]);
                if ($row = $stmt->fetch()) {
                    $results[$table]['final'] = $row['size'];
                    $totals['final'] += $row['size'];
                }
                $this->writeln('Initial est. size: ' . $results[$table]['initial'] . ' MB. Current est size: ' . $results[$table]['final'] . ' MB');
            }
            $this->writeln(PHP_EOL . 'Total initial est. size was: ' . $totals['initial'] . ' MB, total current est. size is: ' . $totals['final'] . ' MB');
        } else {
           $this->writeln('The database in use by this Sugar installation is not MySQL, aborting...'); 
        }
    }
}
