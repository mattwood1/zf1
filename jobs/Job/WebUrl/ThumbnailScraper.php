<?php
/**
 * This job is responsible for scraping thumbnail links and images from webUrls.
 *
 * Scheduled to run every 10 minutes every day.
 */
class Job_WebUrl_ThumbnailScraper extends Job_Abstract
{
    public function run()
    {
        $webUrlsTable = new God_Model_WebURLTable;
        $webUrlsQuery = $webUrlsTable->getInstance()
            ->createQuery('wu')
            ->where('action = ?', God_Model_WebURLTable::ACTION_GET_THUMBNAILS)
            ->limit(500);
        $webUrls = $webUrlsQuery->execute();

        if ($webUrls) {
            foreach ($webUrls as $webUrl) {
                $webURLTable = new God_Model_WebURLTable;
                $webResourceTable = new God_Model_WebResourceTable;
                $webResource = $webResourceTable->getInstance()->findOneBy('id', $webUrl->webResourceId);
                $links = array();
                if ($webResource && $webResource->xpathfilter) {

                    $curl = new God_Model_Curl();
                    $html = $curl->Curl($webUrl->url, null, false, 30, true); // Follow 301
                    if ($webUrl->url != $curl->lastUrl()) {
                        $newWebUrl = $webURLTable->insertLink($curl->lastUrl(), $webResource);
                        $newWebUrl->dateCreated = $webUrl->dateCreated;
                        $newWebUrl->save();
                        $webUrl->linked = $newWebUrl->id;
                    } else {
                        $webUrl->httpStatusCode = $curl->statusCode();
                        $domXPath = new God_Model_DomXPath($html);
                        $links = $domXPath->evaluate($webResource->xpathfilter);
                        $allLinks = $domXPath->evaluate("//a");
                    }
                }

                if ($links) {
                    // Split them - Old code millions already done...
                    $img = array();
                    $href = array();
                    foreach ($links as $link) {
                        $img[] = $link['img'];
                        $href[] = $link['href'];
                        if ($key = array_search($link['href'], $allLinks)) {
                            unset($allLinks[$key]);
                        }
                    }
                    $webUrl->thumbnails = serialize($img);
                    $webUrl->links = serialize($href);
                    $webUrl->action = God_Model_WebURLTable::ACTION_GOT_THUMBNAILS;
                } else {
                    // Mark the webUrl as bad
                    $webUrl->action = God_Model_WebURLTable::ACTION_THUMBNAIL_ISSUE;
                }

                $webUrl->save();
                
                if ($allLinks) {
                    foreach ($allLinks as $allLink) {
                        $webURLTable->insertLink($allLink['href'], $webResource);
                    }
                }
            }
        }
    }
}
