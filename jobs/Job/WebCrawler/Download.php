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
        $curl = new God_Model_Curl();
        $id = 3757903;

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $webCrawlerUrlQuery = $webCrawlerUrlTable->getDisplayQuery();
        $webCrawlerUrlQuery->andWhere('wcu.id = ?', $id);
        $webCrawlerUrls = $webCrawlerUrlQuery->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        foreach ($webCrawlerUrls as $webCrawlerUrl) {
            $thumbnails = God_Model_WebCrawlerUrlTable::getThumbnailsFromData($webCrawlerUrl);

            _d($webCrawlerUrl['id'], $webCrawlerUrl['url']);

            mkdir('/tmp/' . $webCrawlerUrl['id']);

            foreach ($thumbnails as $thumbnail) {

                _d($thumbnail['url']);

                $curl->Curl($thumbnail['url']);

                file_put_contents(
                    '/tmp/' . $webCrawlerUrl['id'] . '/' . basename($thumbnail['url']),
                    $curl->rawdata()
                );
            }
        }
    }
}

