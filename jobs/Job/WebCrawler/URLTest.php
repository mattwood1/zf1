<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_URLTest extends Job_Abstract
{
    public function run()
    {
        $curl = new God_Model_Curl();

        echo $curl->normalizeURL('images/gallery-bottom.jpg', 'http://www.foxhq.com/update/news.php?artc=28352&s=r56tlolts9rkj6a0eqtc7avsc5');
    }
}