<?php

namespace Toothpaste\Sugar\Actions;
use Toothpaste\Sugar\Instance;

class RecordCount
{
    public static function count()
    {
        $total = 0;
        $results = [];
        $db = \DBManagerFactory::getInstance();
        $tables = $db->getTablesArray();
        asort($tables);
        foreach ($tables as $table) {
            $columns = $db->get_columns($table);
            if ($key = array_search('id', array_column($columns, 'name'))) {
                $count_field = 'id';
            } else if ($key = array_search('name', array_column($columns, 'name'))) {
                $count_field = 'name';
            } else {
                $count_field = '*';
            }
            if (!empty($count_field)) {
                $qb = $db->getConnection()->createQueryBuilder();
                $qb->select('COUNT(' . $count_field . ') as count');
                $qb->from($table);
                $res = $qb->execute();
                if ($row = $res->fetch()) {
                    $results[$table] = $row['count'];
                    $total += $results[$table];
                }
                echo $table . ' has ' . $results[$table] . ' records' . PHP_EOL;
            }
        }
        $results['total_db_records'] = $total;

        echo 'The database has in total ' . $total . ' records' . PHP_EOL;

        echo 'JSON output:' . PHP_EOL . PHP_EOL;
        echo json_encode($results);
        echo PHP_EOL . PHP_EOL;

        echo 'CSV output:' . PHP_EOL . PHP_EOL;
        echo '"table","count"'.PHP_EOL;
        foreach ($results as $table => $count) {
            echo '"' . $table . '","' . $count . '"' . PHP_EOL;
        }
        echo PHP_EOL . PHP_EOL;
    }
}
