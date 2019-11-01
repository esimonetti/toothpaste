<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Logic;
use Toothpaste\Sugar\Instance;
use Toothpaste\Sugar;

class SugarBPMCleanup extends Sugar\BaseLogic
{
    //protected $cas_flow_status = ['CLOSED', 'TERMINATED'];
    protected $cas_status = ['COMPLETED', 'TERMINATED'];

    public function delete($monthsToKeep = 3)
    {
        // delete all old records, aside from the initiating one, to keep the state of the process
        /*
        // OLD - DELETE FROM pmse_bpm_flow WHERE cas_id IN (SELECT cas_id from pmse_inbox WHERE cas_status in ('COMPLETED', 'TERMINATED') AND date_modified < '2019-01-01 00:00:00') AND cas_index > 1 and cas_flow_status IN ('CLOSED', 'TERMINATED')
        DELETE FROM pmse_bpm_flow WHERE cas_id IN (SELECT cas_id from pmse_inbox WHERE cas_status in ('COMPLETED', 'TERMINATED') AND date_modified < '2019-01-01 00:00:00') AND cas_index > 1
        */

        // delete matching inbox records the deleted records from the previous process execution UI
        /*
        // OLD - DELETE FROM pmse_inbox WHERE cas_status in ('COMPLETED', 'TERMINATED') AND date_modified < '2019-01-01 00:00:00' AND cas_id in (SELECT cas_id FROM pmse_bpm_flow WHERE cas_flow_status IN ('CLOSED', 'TERMINATED') AND cas_index = '1')
        DELETE FROM pmse_inbox WHERE cas_status in ('COMPLETED', 'TERMINATED') AND date_modified < '2019-01-01 00:00:00' AND cas_id in (SELECT cas_id FROM pmse_bpm_flow WHERE cas_index = '1')
        */

        if ($monthsToKeep < 0) {
            $monthsToKeep = 3;
        }

        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));
        $datetime->modify('-' . $monthsToKeep . ' months');
        $deleteRecordsBeforeDate = $datetime->format('Y-m-d H:i:s');

        $this->writeln('Deleting the majority of old SugarBPM flow records and their matching process inbox records');
        $this->writeln('One record per process flow execution will be kept, to maintain previous processes execution state');
        $this->writeln('Deleting records older than ' . $deleteRecordsBeforeDate . ' in UTC');

        $dbConnection = \DBManagerFactory::getInstance()->getConnection();

        $this->write('Deleting unnecessary records from SQL table pmse_bpm_flow. The process might take a while, please wait... ');

        // second query first
        $b2 = $dbConnection->createQueryBuilder();
        $b2->select(['cas_id'])
            ->from('pmse_inbox')
            ->where(
                $b2->expr()->in(
                    'cas_status',
                    $b2->createPositionalParameter($this->cas_status, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                )
            )
            ->andWhere(
                $b2->expr()->lt(
                    'date_modified',
                    $b2->createPositionalParameter($deleteRecordsBeforeDate)
                )
            );

        $b1 = $dbConnection->createQueryBuilder();
        $b1->delete('pmse_bpm_flow')
            ->where(
                $b1->expr()->in(
                    'cas_id',
                    $b1->importSubQuery($b2)
                )
            )
            // here we keep cas_index = 1 (the first record of every process) so that if a process has to trigger only once, it has the reference point
            ->andWhere(
                $b1->expr()->gt(
                    'cas_index',
                    $b1->createPositionalParameter(1)
                )
            );

        //$this->writeln('Executing ' . $b1->getSQL());
        //$this->writeln('Executing ' . print_r($b1->getParameters(), true));

        /*
        Executing DELETE FROM pmse_bpm_flow WHERE (cas_id IN (SELECT cas_id FROM pmse_inbox WHERE (cas_status IN (?)) AND (date_modified < ?))) AND (cas_index > ?)
        Executing Array
        (
            [1] => Array
                (
                    [0] => COMPLETED
                    [1] => TERMINATED
                )

            [2] => 2019-10-19 09:54:16
            [3] => 1
        )
        */

        $b1->execute();
        $this->writeln('done.');


        $this->write('Deleting unnecessary records from SQL table pmse_inbox. The process might take a while, please wait... ');

        // second query first
        $b2 = $dbConnection->createQueryBuilder();
        $b2->select(['cas_id'])
            ->from('pmse_bpm_flow')
            ->where(
                $b2->expr()->eq(
                    'cas_index',
                    $b2->createPositionalParameter(1)
                )
            );

        $b1 = $dbConnection->createQueryBuilder();
        $b1->delete('pmse_inbox')
            ->where(
                $b1->expr()->in(
                    'cas_id',
                    $b1->importSubQuery($b2)
                )
            )
            ->andWhere(
                $b1->expr()->in(
                    'cas_status',
                    $b1->createPositionalParameter($this->cas_status, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY)
                )
            )
            ->andWhere(
                $b1->expr()->lt(
                    'date_modified',
                    $b1->createPositionalParameter($deleteRecordsBeforeDate)
                )
            );

        //$this->writeln('Executing ' . $b1->getSQL());
        //$this->writeln('Executing ' . print_r($b1->getParameters(), true));

        /*
        Executing DELETE FROM pmse_inbox WHERE (cas_id IN (SELECT cas_id FROM pmse_bpm_flow WHERE cas_index = ?)) AND (cas_status IN (?)) AND (date_modified < ?)
        Executing Array
        (
            [1] => 1
            [2] => Array
                (
                    [0] => COMPLETED
                    [1] => TERMINATED
                )

            [3] => 2019-10-19 09:54:16
        )
        */

        $b1->execute();
        $this->writeln('done.');
    }
}
