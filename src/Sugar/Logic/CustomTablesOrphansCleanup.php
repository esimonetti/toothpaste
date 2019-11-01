<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class CustomTablesOrphansCleanup extends Sugar\BaseLogic
{
    protected $limit = 1000;

    public function cleanup()
    {
        global $beanList, $app_list_strings;
        $fullModuleList = array_merge($beanList, $app_list_strings['moduleList']);

        $db = \DBManagerFactory::getInstance();

        $processedTables = [];
        foreach ($fullModuleList as $module => $label) {
            $bean = \BeanFactory::newBean($module);
            if (empty($processedTables[$bean->table_name]) && $bean instanceof \SugarBean && method_exists($bean, 'hasCustomFields') && $bean->hasCustomFields()) {
                $processedTables[$bean->table_name] = $bean->module_name;
                $this->writeln('The SQL table ' . $bean->table_name . ' has custom fields on ' . $bean->get_custom_table_name() . ' seeking orphans...');
                $hasRecords = true;
                $counter = 0;
                while ($hasRecords) {
                    $b1 = $db->getConnection()->createQueryBuilder();
                    $b1->select(['cstm_tbl.id_c'])->from($bean->get_custom_table_name(), 'cstm_tbl');
                    $b1->leftJoin('cstm_tbl', $bean->table_name, 'core_tbl', 'cstm_tbl.id_c = core_tbl.id');
                    $b1->where($b1->expr()->isNull('core_tbl.id'));
                    $b1->setMaxResults($this->limit);
                    $this->writeln('Executing ' . $b1->getSQL());
                    $res = $b1->execute();
            
                    $currentOrphans = [];
                    while ($row = $res->fetch()) {
                        $currentOrphans[] = $row['id_c'];
                        $counter++;
                    }
                    if (!empty($currentOrphans)) {
                        $hasRecords = true;
                        $b2 = $db->getConnection()->createQueryBuilder();
                        $b2->delete($bean->get_custom_table_name());
                        $b2->where(
                            $b2->expr()->in(
                                'id_c',
                                $b2->createPositionalParameter($currentOrphans, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                            )
                        );
                        $b2->execute();
                        $this->writeln('Deleted ' . $counter . ' records from the custom SQL table ' . $bean->get_custom_table_name());
                    } else {
                        $hasRecords = false;
                    }
                }
                if(!empty($orphans)) {
                    $this->writeln('');
                    $this->writeln('Found and deleted ' . count($orphans) . ' orphan record(s) from the custom SQL table ' . $bean->get_custom_table_name());
                }
            }
        }
    }
}
