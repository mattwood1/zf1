<?php
/**
 * This job is responsible for crawling known websites.
 *
 * Scheduled to run every 10 minutes every day.
 */
class Job_WebUrl_ResourceScraper extends Job_Abstract
{
    public function run()
    {
        $webResourceTable = new God_Model_WebResourceTable;
        $webResourceQuery = $webResourceTable->getInstance()
            ->createQuery('wr')
            ->where('sitescan = ?', 1)
            ->andWhere('nextCheck < ?', date("Y-m-d H:i:s"))
            ->orderBy('nextCheck ASC')
            ->limit(5);
        $webResources = $webResourceQuery->execute();

        foreach ($webResources as $webResource) {
            $domain = "http://www." . $webResource->website;

            $curl = new God_Model_Curl();
            $html = $curl->Curl($webResource->sitescanurl);

            $domXPath = new God_Model_DomXPath($html);
            $links = $domXPath->evaluate($webResource->sitescanxpath);

            // Process Links or Update Frequency Check
            if ($webResource->checksum == md5(serialize($links))) {
                // Update frequency time if links have not changed
                $timeMatch = array();
                preg_match("~^\+([\d]+)\shours~", $webResource->frequency, $timeMatch);
                $hours = $timeMatch[1] +1;
                if ($hours > 24) $hours = 24;
                $webResource->frequency = '+'.$hours.' hours';

            } else {
                $webResource->checksum = md5(serialize($links));

                preg_match("~^\+([\d]+)\shours~", $webResource->frequency, $timeMatch);
                $hours = ceil($timeMatch[1]/2);
                $webResource->frequency = '+'.$hours.' hours';

                foreach ($links as $link) {
                    $webURLTable = new God_Model_WebURLTable;
                    if (!preg_match("~^http:\/\/~", $link["href"])) {
                        $link["href"] = $domain.$link["href"];
                    }
                    $webURLTable->insertLink($link["href"], $webResource->id);
                }
            }

            // Update the time
            $webResource->nextCheck = date("Y-m-d H:i:s", strtotime($webResource->frequency));
            $webResource->save();
        }

    }
}