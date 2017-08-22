<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_Url extends Job_Abstract
{
    public function run()
    {
        checkCPULoad(God_Model_WebCrawlerUrl::CPULOAD);

        file_put_contents('/tmp/Url.txt', 'New set of data' . "\r\n", FILE_APPEND);

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $webCrawlerUrls = $webCrawlerUrlTable->getQuery()->execute();

        foreach ($webCrawlerUrls as $webCrawlerUrl) {

            checkCPULoad(God_Model_WebCrawlerUrl::CPULOAD);

//            _d($webCrawlerUrl);
            $start = microtime(true);

            $webCrawlerUrl->processUrl();

            $duration = microtime(true) - $start;
            $text = 'Completed - ' . $duration . "\r\n\r\n";

            file_put_contents('/tmp/Url.txt', $text, FILE_APPEND);
        }
    }
}
