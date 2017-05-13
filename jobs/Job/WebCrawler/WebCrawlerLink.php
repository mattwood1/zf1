<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_WebCrawlerLink extends Job_Abstract
{
    public function run()
    {
        $cpuload = 1.4;
        checkCPULoad($cpuload);
        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $curl = new God_Model_Curl();

        // Get CURL Headers for new urls
        $webCrawlerLinkTable = new God_Model_WebCrawlerLinkTable();
        $webCrawlerLinkQuery = $webCrawlerLinkTable->getInstance()
            ->createQuery('wl')
            ->where('url_id = ?', 0)
            ->orderBy('parent_url_id asc')
            ->limit(500);
//        _dexit($webCrawlerLinkQuery);
        $webCrawlerLinks = $webCrawlerLinkQuery->execute();

        foreach ($webCrawlerLinks as $webCrawlerLink) {
            checkCPULoad($cpuload);

            $curl->Curl($webCrawlerLink->link, null,false, 10, true, true);
            $webUrl = $webCrawlerUrlTable->findInsert($curl);
            $webCrawlerLink->url_id = $webUrl->id;
            $webCrawlerLink->save();

        }
    }
}