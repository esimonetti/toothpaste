<?php

// Enrico Simonetti
// enricosimonetti.com

namespace Toothpaste\Sugar\Actions;
use Toothpaste\Sugar;

class MassRetrieverApi extends Sugar\Rest
{
    protected $page;

    public $maxSleepTimeUs = 0;//5000000;

    protected function delay()
    {
        if ($this->maxSleepTimeUs > 0) {
            $time = rand(0, $this->maxSleepTimeUs);
            usleep($time);
        }
    }

    public function initiateRetrieve($module, $filter = [], $limit = 50, $outputDir = '', $offset = 0)
    {
        //$count = $this->countRecords($module, $filter);
        $resp = $this->retrieveRecords($module, $filter, $limit, $offset);
        $this->saveRecordsToDisk($module, $outputDir, $resp['records']);
        $savedCount = count($resp['records']);
        while ($resp['next_offset'] !== -1) {
            $this->delay();
            $resp = $this->retrieveRecords($module, $filter, $limit, $resp['next_offset']);
            $this->saveRecordsToDisk($module, $outputDir, $resp['records']);
            $savedCount += count($resp['records']);
        }

        return $savedCount;
    }

    protected function retrieveRecords($module, $filter, $limit, $offset)
    {
        return $this->completeRestCall('GET', '/' . $module, ['filter' => $filter, 'max_num' => $limit, 'offset' => $offset, 'order_by' => 'date_modified:DESC']);
    }

    protected function countRecords($module, $filter)
    {
        return $this->completeRestCall('GET', '/' . $module . '/count', ['filter' => $filter, 'order_by' => 'date_modified:DESC']);
    }

    protected function saveRecordsToDisk($module, $outputDir, $records)
    {
        $destDir = $outputDir . '/' . strtolower($module);
        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        }
       
        $fileName = $destDir . '/' . strtolower($module) . '_' . microtime(true). '.json';
        file_put_contents($fileName, json_encode($records));
    }
}
