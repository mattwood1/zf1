<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_WebCrawlerUrl extends Job_Abstract
{
    public function run()
    {
        $cpuload = 1.4;
        checkCPULoad($cpuload);

        $webCrawlerUrlTable = new God_Model_WebCrawlerUrlTable();
        $curl = new God_Model_Curl();

        $webCrawlerUrlQuery = $webCrawlerUrlTable->getInstance()
            ->createQuery('wu')
            ->leftJoin('wu.domain wd')
            ->where('(followed = ? or frequency is not null)', 0)
            ->andWhere('contenttype like ?', '%text/html%')
            ->andWhere('(date < ? or date is null)', date("Y-m-d H:i:s"))
            ->andWhere('allowed = 1')
            ->limit(50);
        $webCrawlerUrls = $webCrawlerUrlQuery->execute();

        foreach ($webCrawlerUrls as $webCrawlerUrl) {
            checkCPULoad($cpuload);

            $curl->Curl($webCrawlerUrl->url, null, null, 10, true);
            $content = $curl->rawdata();

            $dom = new DOMDocument();
            @$dom->loadHTML($content);

            $links = array();

            $linkspath = new DOMXPath($dom);

            $aTag = $linkspath->evaluate('//a');
            for ($i = 0; $i < $aTag->length; $i++) {
                $link = $aTag->item($i);
                $href = $link->getAttribute('href');
                $links[] = trim($curl->normalizeURL($href, $webCrawlerUrl->url));
            }

            $imgTag = $linkspath->evaluate('//img');
            for ($i = 0; $i < $imgTag->length; $i++) {
                $image = $imgTag->item($i);
                $src = $image->getAttribute('src');
                $links[] = trim($curl->normalizeURL($src, $webCrawlerUrl->url));
            }

            $links = array_unique($links);
            $linkChunks = array_chunk($links, 1000);

            // Known links
            $knownLinks = array();
            foreach ($linkChunks as $linkChunk) {
                $dblinkQuery = God_Model_WebCrawlerLinkTable::getInstance()
                    ->createQuery('wc')
                    ->whereIn('link', $linkChunk);
                $dblinks = $dblinkQuery->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

                foreach ($dblinks as $dblink) {
                    $knownLinks[] = $dblink['link'];
                }
            }

            $knownLinks = array_unique($knownLinks);
            $knownLinks = array_filter($knownLinks);

            $linksMissing = array_diff($links, $knownLinks);

            if ($linksMissing) {
                foreach ($linksMissing as $linkMissing) {

                    if (strlen($linkMissing) <= 1000) {
                        $newLink = new God_Model_WebCrawlerLink();
                        $newLink->fromArray(array(
                            'link' => trim($linkMissing),
                            'parent_url_id' => $webCrawlerUrl->id
                        ));
                        $newLink->save();
                    }

                }
            }

            if ($webCrawlerUrl->frequency) {
                $webCrawlerUrl->date = date('Y-m-d H:i:s', strtotime($webCrawlerUrl->frequency));
            }

            $webCrawlerUrl->followed = 1;
            $webCrawlerUrl->save();

        }
    }
}