<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_Url extends Job_Abstract
{
    public function run()
    {
        $cpuload = 1;
        checkCPULoad($cpuload);

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $webCrawlerUrlQuery = $webCrawlerUrlTable->getInstance()
            ->createQuery('wu')
            ->leftJoin('wu.domain wd')
            ->where('(wu.followed = ? or wu.frequency is not null)', 0)
            ->andWhere('wu.contenttype like ?', '%text/html%')
            ->andWhere('(wu.date < ? or wu.date is null)', date("Y-m-d H:i:s"))
            ->andWhere('wd.allowed = 1')
            ->orderBy('wu.date DESC')
            ->limit(100);
        $webCrawlerUrls = $webCrawlerUrlQuery->execute();

        foreach ($webCrawlerUrls as $webCrawlerUrl) {

            $webCrawlerUrl->processUrl();

        }
    }
}
