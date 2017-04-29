<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_WebCrawlerLink extends Job_Abstract
{
    public function run()
    {
        checkCPULoad();
        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $curl = new God_Model_Curl();

        // Get CURL Headers for new urls
        $webCrawlerLinkTable = new God_Model_WebCrawlerLinkTable();
        $webCrawlerLinkQuery = $webCrawlerLinkTable->getInstance()
            ->createQuery('wl')
            ->where('url_id = ?', 0)
            ->limit(400);
        $webCrawlerLinks = $webCrawlerLinkQuery->execute();

        foreach ($webCrawlerLinks as $webCrawlerLink) {
            checkCPULoad();

            $curl->Curl($webCrawlerLink->link, null,false, 10, true, true);
            $webUrl = $webCrawlerUrlTable->findInsert($curl);
            $webCrawlerLink->url_id = $webUrl->id;
            $webCrawlerLink->save();

        }
    }
}