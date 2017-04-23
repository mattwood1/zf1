<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_WebCrawler extends Job_Abstract
{
    public function run()
    {
        checkCPULoad();

        // Get CURL Headers for new urls
        $webCrawlerTable = new God_Model_WebCrawlerTable();
        $webCrawlerQuery = $webCrawlerTable->getInstance()
            ->createQuery('wc')
            ->where('url = ?', '')
            ->limit(1000);
        $webCrawlerLinks = $webCrawlerQuery->execute();

        foreach ($webCrawlerLinks as $webCrawlerLink) {
            checkCPULoad();

            $curl = new God_Model_Curl();

            $curl->Curl($webCrawlerLink->link, null,false, 10, true);

            $webCrawlerLink->statuscode = $curl->statusCode();
            $webCrawlerLink->contenttype = $curl->contentType();
            $webCrawlerLink->contentlenght = $curl->contentLength();
            $webCrawlerLink->url = $curl->lastUrl();

            $webCrawlerLink->save();

        }

        // Get Curl contents for urls not followed and later scheduled
        $webCrawlerQuery = $webCrawlerTable->getinstance()
            ->createQuery('wc')
            ->where('followed = ?', 0)
            ->limit(10);
        $webCrawlerUrls = $webCrawlerQuery->execute();

        foreach ($webCrawlerUrls as $webCrawlerUrl) {

            $curl = new God_Model_Curl();

            $curl->Curl($webCrawlerUrl->url, null, null, 10, true);

            $content = $curl->rawdata();

// Should be in the DOMXPath Model, but understanding how to make it a model is tricky

            $dom = new DOMDocument();
            @$dom->loadHTML($content);

            $linkspath = new DOMXPath($dom);
            $links = $linkspath->evaluate('//a');

            for ($i = 0; $i < $links->length; $i++) {
                $link = $links->item($i);
                $href = $link->getAttribute('href');

                $href = $curl->normalizeURL($href, $webCrawlerUrl->url);

                $dblink = God_Model_WebCrawlerTable::getInstance()->findBy('link', $href);
                if (count($dblink) == 0) {

                    $newLink = new God_Model_WebCrawler();
                    $newLink->fromArray(array(
                        'link' => $href,
                        'parent' => $webCrawlerUrl->id
                    ));
                    $newLink->save();

                }

            }

            $images = $linkspath->evaluate('//img');

            for ($i = 0; $i < $images->length; $i++) {
                $image = $images->item($i);
                $src = $image->getAttribute('src');

                $src = $curl->normalizeURL($src, $webCrawlerUrl->url);

                $dblink = God_Model_WebCrawlerTable::getInstance()->findBy('link', $src);
                if (count($dblink) == 0) {

                    $newLink = new God_Model_WebCrawler();
                    $newLink->fromArray(array(
                        'link' => $src,
                        'parent' => $webCrawlerUrl->id
                    ));
                    $newLink->save();

                }
            }

//            var_dump($content, $links);

            $webCrawlerUrl->followed = 1;
            $webCrawlerUrl->save();

        }

    }
}