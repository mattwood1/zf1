<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_Url extends Job_Abstract
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
            $sql = "delete from webcrawlerUrlLink_ref where id in (" . implode(',', $ids) . ")";
            $conn->execute($sql);
        }

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $webCrawlerUrls = $webCrawlerUrlTable->getQuery()->execute();

        foreach ($webCrawlerUrls as $webCrawlerUrl) {

            checkCPULoad();

//            _d($webCrawlerUrl);
            $webCrawlerUrl->processUrl();
        }
    }
}
