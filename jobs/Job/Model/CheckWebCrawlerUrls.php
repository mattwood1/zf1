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

            checkCPULoad(1.7);

            $modelName->verifyWebCrawlerUrls();
            $modelName->linkWebCrawlerUrls();

            $modelName->webcrawler_updated = date("Y-m-d H:i:s");
            $modelName->model->webcrawler_updated = date("Y-m-d H:i:s");
            $modelName->save();
        }
    }
}
