<?php
/**
 * Created by PhpStorm.
 * User: mwood
 * Date: 30/08/17
 * Time: 18:56
 */

class Job_WebCrawler_Download extends Job_Abstract
{
    public function run()
    {
        $id = 3757903;

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $webCrawlerUrlQuery = $webCrawlerUrlTable->getDisplayQuery();
        $webCrawlerUrlQuery->andWhere('wcu.id = ?', $id);
        $webCrawlerUrl = $webCrawlerUrlQuery->execute();

        _d($webCrawlerUrl);

    }
}

