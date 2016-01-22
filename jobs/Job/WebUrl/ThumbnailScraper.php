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
            ->orderBy('id ASC')
            ->limit(300);
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
                        if ($webResource->xpathfilter) {
                            $domXPath = new God_Model_DomXPath($html);
                            $links = $domXPath->evaluate($webResource->xpathfilter);
                            $imageLinks = $domXPath->evaluate(str_replace("/img", "", $webResource->xpathfilter));
                            $allLinks = $domXPath->evaluate("//a");
                        }
                    }
                }
                
                $imageLinkHref = array();
                if ($imageLinks) {
                    foreach ($imageLinks as $imageLink) {
                        $imageLinkHref[] = $imageLink['href'];
                    }
                }
                
                if ($allLinks) {
                    foreach ($allLinks as $allLink) {
                        $allLinkHref[] = $allLink['href'];
                    }
                }

                if ($links) {
                    // Split them - Old code millions already done...
                    $img = array();
                    $href = array();
                    foreach ($links as $link) {
                        $img[] = $link['img'];
                        $href[] = $link['href'];
                    }
                    
                    
                    $webUrl->thumbnails = serialize($img);
                    $webUrl->links = serialize($href);
                    $webUrl->action = God_Model_WebURLTable::ACTION_GOT_THUMBNAILS;
                } else {
                    // Mark the webUrl as bad
                    $webUrl->action = God_Model_WebURLTable::ACTION_THUMBNAIL_ISSUE;
                }

                $webUrl->save();
                /*
                if ($allLinkHref) {
                    foreach ($allLinkHref as $allLink) {
                        if (array_search($allLink, $imageLinkHref)) continue;
                        if (preg_match("~.*.jpg|.*.png|javascript|facebook|twitter|tumblr|reddit|plus.google|stumbleupon|digg.com|cqcounter|ccbill|mailto:\#respond|\#comment~i", $allLink)) {
                            continue;
                        }
                        if (preg_match("~^\?.*|^\/.*~", $allLink)) {
                            $urlparse = parse_url($webUrl->url);
                            $allLink = $urlparse['scheme'].'://'.$urlparse['host'].$urlparse['path'].$allLink;
                            $allLink = str_replace("//", "/", $allLink);
                        }
                        _d($webUrl->url);
                        _d('adding '.$allLink);
                        $webURLTable->insertLink($allLink, $webResource, God_Model_WebURLTable::ACTION_GET_THUMBNAILS);
                    }
                }
                 */
            }
        }
    }
}
