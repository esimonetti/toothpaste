<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Actions;
use Toothpaste\Sugar;

class MassRetrieverApi extends Sugar\Rest
{
    public $maxSleepTimeUs = 0; // 5000000 is 5 seconds, for now set to 0 to not have delays;

    protected function delay()
    {
        if ($this->maxSleepTimeUs > 0) {
            $time = rand(0, $this->maxSleepTimeUs);
            usleep($time);
        }
    }

    public function initiateRetrieve($module, $filter = '[]', $limit = 50, $outputDir = '', $offset = 0)
    {
        $filter = json_decode($filter, true);
        $this->writeln('Initiating retrieve for: ' . $module);
        $totalCount = $this->countRecords($module, $filter);
        $this->writeln('The are a total of ' . $totalCount['record_count'] . ' (minus the offset of ' . $offset . ') records available for: ' . $module . ' with filter conditions: ' . json_encode($filter));

        $resp = $this->retrieveRecords($module, $filter, $limit, $offset);
        $savedCount = count($resp['records']);
        $this->writeln('The system returned ' . $savedCount . ' records for this api call. Retrieved a total of ' . $savedCount . ' out of ' . $totalCount['record_count'] . ' (minus the offset of ' . $offset . ') records.');
        $this->saveRecordsToDisk($module, $outputDir, $resp['records']);

        while ($resp['next_offset'] !== -1) {
            $this->delay();
            $resp = $this->retrieveRecords($module, $filter, $limit, $resp['next_offset']);
            $batchCount = count($resp['records']);
            $savedCount += $batchCount;
            $this->writeln('The system returned ' . $batchCount . ' records for this api call. Retrieved a total of ' . $savedCount . ' out of ' . $totalCount['record_count'] . ' (minus the offset of ' . $offset . ') records.');
            $this->saveRecordsToDisk($module, $outputDir, $resp['records']);
        }

        return $savedCount;
    }

    protected function retrieveRecords($module, $filter, $limit, $offset)
    {
        $this->writeln('Retrieving ' . $limit . ' records with ' . $offset . ' as offset for module: ' . $module . ' with filter conditions: ' . json_encode($filter));
        return $this->completeRestCall('GET', '/' . $module, ['filter' => $filter, 'max_num' => $limit, 'offset' => $offset, 'order_by' => 'date_modified:DESC']);
    }

    protected function countRecords($module, $filter)
    {
        $this->writeln('Executing the count of records for module: ' . $module . ' with filter conditions: ' . json_encode($filter));
        return $this->completeRestCall('GET', '/' . $module . '/count', ['filter' => $filter, 'order_by' => 'date_modified:DESC']);
    }

    protected function saveRecordsToDisk($module, $outputDir, $records)
    {
        $destDir = $outputDir . '/' . strtolower($module);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }
       
        $fileName = $destDir . '/' . strtolower($module) . '_' . microtime(true). '.json';

        $this->writeln('Saving records batch to disk on ' . $fileName);
        file_put_contents($fileName, json_encode($records));
    }
}
