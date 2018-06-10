<?php
/**
 * This job is responsible for Updating Models WebCrawlewrUrls based on the webcrawler_updated field
 *
 * Scheduled to run every minute
 */
class Job_Model_CheckWebCrawlerUrls extends Job_Abstract
{
    public function run()
    {
        $modelNames = God_Model_ModelNameTable::getModelNameForWebCrawlerUpdate();

        foreach ($modelNames as $modelName) {

            checkCPULoad();

            $logfile = fopen('/tmp/WC_ModelNameUpdate_' . date("Y-m-d") . '.txt', 'a');

            fwrite($logfile, date("H:i:s") . ' ' . $modelName->name . ' (' . $modelName->webcrawler_updated . ')' . "\n");

            $modelName->verifyWebCrawlerUrls();

            fwrite($logfile, date("H:i:s") . ' ' . 'Checked existing URL links' . "\n");

            $modelName->linkWebCrawlerUrls();

            fwrite($logfile, date("H:i:s") . ' ' . 'Checked non-existing URL links' . "\n");

            $modelName->webcrawler_updated = date("Y-m-d H:i:s");
            $modelName->model->webcrawler_updated = date("Y-m-d H:i:s");
            $modelName->save();

            fclose($logfile);
        }
    }
}
