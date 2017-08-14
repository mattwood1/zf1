<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_Url extends Job_Abstract
{
    public function run()
    {
        $cpuload = 1.5;
        checkCPULoad($cpuload);

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $webCrawlerUrls = $webCrawlerUrlTable->getQuery()->execute();

        $webCrawlerUrls = God_Model_WebCrawlerUrlTable::getInstance()->findBy('id', 2947388);

        foreach ($webCrawlerUrls as $webCrawlerUrl) {

            checkCPULoad($cpuload);

            $webCrawlerUrl->processUrl();
        }
    }
}
