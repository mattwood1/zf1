<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_Link extends Job_Abstract
{
    public function run()
    {
        checkCPULoad();

        $conn = Doctrine_Manager::getInstance()->connection();
        $sql = "select wculr.id from webcrawlerUrlLink_ref wculr 
                left join webcrawlerUrls wcu on wculr.url_id = wcu.id 
                where wcu.id is null limit 1000";
        $query = $conn->execute($sql);
        $ids = array();
        foreach ($query->fetchAll() as $row) {
            $ids[] = $row['id'];
        }

        if ($ids) {
            foreach ($ids as $linkid) {
                if ($link = God_Model_WebCrawlerUrlLinkTable::getInstance()->find($linkid)) {
                    $link->delete();
                }
            }
        }

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $curl = new God_Model_Curl();

        // Get CURL Headers for new urls
        $webCrawlerLinkTable = new God_Model_WebCrawlerLinkTable();
        $webCrawlerLinkQuery = $webCrawlerLinkTable->getInstance()
            ->createQuery('wl')
            ->select('*')
//            ->leftJoin('wl.parent_link as wll')
            ->where('wl.url_id = ?', 0)
//            ->orderBy('wl.priority desc, wll.parent_url_id asc')
            ->orderBy('wl.priority asc, wl.id asc')
//            ->groupBy('wl.id')
            ->limit(100);
//        _dexit($webCrawlerLinkQuery);

        $webCrawlerLinks = $webCrawlerLinkQuery->execute();

        foreach ($webCrawlerLinks as $webCrawlerLink) {

            checkCPULoad();

            $curl->Curl($webCrawlerLink->link, $webCrawlerLink->parent_url->url,false, 10, true, true);
            $webUrl = $webCrawlerUrlTable->findInsert($curl);
            $webCrawlerLink->url_id = $webUrl->id;
            $webCrawlerLink->save();

        }
    }
}
