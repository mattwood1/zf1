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
            ->limit(100);
        $webCrawlerLinks = $webCrawlerQuery->execute();

        foreach ($webCrawlerLinks as $webCrawlerLink) {
            checkCPULoad();

            $curl = new God_Model_Curl();
            $curl->Curl($webCrawlerLink->link, null,false, 10, true);

            $webCrawlerLink->statuscode = $curl->statusCode();
            $webCrawlerLink->contenttype = $curl->contentType();
            $webCrawlerLink->contentlength = $curl->contentLength();
            $webCrawlerLink->url = $curl->lastUrl();
            $webCrawlerLink->save();
        }

        // Get Curl contents for urls not followed and later scheduled
        $webCrawlerQuery = $webCrawlerTable->getInstance()
            ->createQuery('wc')
            ->where('(followed = ? or frequency is not null)', 0)
            ->andWhere('contenttype like ?', '%text/html%')
            ->andWhere('(date < ? or date is null)', date("Y-m-d H:i:s"));
//            ->limit(50);
        $webCrawlerUrls = $webCrawlerQuery->execute();

        foreach ($webCrawlerUrls as $webCrawlerUrl) {
            checkCPULoad();

            $curl = new God_Model_Curl();
            $curl->Curl($webCrawlerUrl->url, null, null, 10, true);
            $content = $curl->rawdata();

// Should be in the DOMXPath Model, but understanding how to make it a model is tricky

            $dom = new DOMDocument();
            @$dom->loadHTML($content);

            $links = array();

            $linkspath = new DOMXPath($dom);

            $aTag = $linkspath->evaluate('//a');
            for ($i = 0; $i < $aTag->length; $i++) {
                $link = $aTag->item($i);
                $href = $link->getAttribute('href');
                $links[] = $curl->normalizeURL($href, $webCrawlerUrl->url);
            }

            $imgTag = $linkspath->evaluate('//img');
            for ($i = 0; $i < $imgTag->length; $i++) {
                $image = $imgTag->item($i);
                $src = $image->getAttribute('src');
                $links[] = $curl->normalizeURL($src, $webCrawlerUrl->url);
            }

            // Known links
            $knownLinks = array();
            $dblinkQuery = God_Model_WebCrawlerTable::getInstance()
                ->createQuery('wc')
                ->whereIn('link', $links)
                ->orWhereIn('url', $links);
            $dblinks = $dblinkQuery->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

            foreach ($dblinks as $dblink) {
                $knownLinks[] = $dblink['link'];
                $knownLinks[] = $dblink['url'];
            }
            $knownLinks = array_filter($knownLinks);
            $knownLinks = array_unique($knownLinks);

            $linksMissing = array_diff($links, $knownLinks);

            if ($linksMissing) {
                foreach ($linksMissing as $linkMissing) {

                    $parseLink = parse_url($linkMissing);
                    $parseUrl = parse_url($webCrawlerUrl->url);

                    $parent = 0;
                    if (array_key_exists('host', $parseLink) && array_key_exists('host', $parseUrl) && $parseLink['host'] == $parseUrl['host']) {
                        $parent = $webCrawlerUrl->id;
                    }
                    else {
                        $parent = $webCrawlerUrl->id;
                    }

                    $newLink = new God_Model_WebCrawler();
                    $newLink->fromArray(array(
                        'link' => $linkMissing,
                        'parent' => $parent
                    ));
                    $newLink->save();

                }
            }

//            var_dump($content, $links);

            if ($webCrawlerUrl->frequency) {
                $webCrawlerUrl->date = date('Y-m-d H:i:s', strtotime($webCrawlerUrl->frequency));
            }

            $webCrawlerUrl->followed = 1;
            $webCrawlerUrl->save();

        }

    }
}