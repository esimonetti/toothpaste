<?php

namespace Toothpaste\Sugar\Actions;
use Toothpaste\Sugar\Instance;

class MySQLOptimize
{
    public static function isMySQL()
    {
        $db = \DBManagerFactory::getInstance();
        return ($db->dbType === 'mysql') ? true : false;
    }
    
    public static function executeTablesOptimize()
    {
        if (self::isMySQL()) {
            echo 'Running optimize on all database tables' . PHP_EOL;
            $db = \DBManagerFactory::getInstance();
            $tables = $db->getTablesArray();
            asort($tables);
            foreach ($tables as $table) {
                // store initial size
                $query = 'select ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size from information_schema.TABLES where TABLE_NAME = ?';
                $stmt = $db->getConnection()->executeQuery($query, array($table));
                if ($row = $stmt->fetch()) {
                    $results[$table]['initial'] = $row['size'];
                    $totals['initial'] += $row['size'];
                }
                $query = 'OPTIMIZE TABLE ' . $table;
                echo 'Running query ' . $query . '... ';
                $stmt = $db->getConnection()->executeQuery($query);
                echo 'done.';
                // check final size
                $query = 'select ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as size from information_schema.TABLES where TABLE_NAME = ?';
                $stmt = $db->getConnection()->executeQuery($query, array($table));
                if ($row = $stmt->fetch()) {
                    $results[$table]['final'] = $row['size'];
                    $totals['final'] += $row['size'];
                }
                echo ' Initial est. size: ' . $results[$table]['initial'] . ' MB. Current est size: ' . $results[$table]['final'] . ' MB' . PHP_EOL;
            }
            echo PHP_EOL . 'Total initial est. size was: ' . $totals['initial'] . ' MB, total current est. size is: ' . $totals['final'] . ' MB' . PHP_EOL;
        } else {
           echo 'The database in use by this Sugar installation is not MySQL, aborting...' . PHP_EOL; 
        }
    }
}
