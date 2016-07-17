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
        checkCPULoad();
        
        $webUrlsQuery = God_Model_WebURLTable::getInstance()
            ->createQuery('wu')
            ->where('action = ?', God_Model_WebURLTable::ACTION_GET_THUMBNAILS)
            // Pick up thumbnail issues with status 0 in older than a month.
            ->orWhere('action = ? and httpStatusCode = 0 and dateUpdated = "0000-00-00 00:00:00" ', God_Model_WebURLTable::ACTION_THUMBNAIL_ISSUE)
            ->orderBy('dateCreated DESC')
            ->limit(100);
        echo "Getting list...\n\n";
        $webUrls = $webUrlsQuery->execute();
        
        if ($webUrls) {
            echo "Processing " . count($webUrls) . " urls\n\n";
            
            foreach ($webUrls as $webUrl) {
                checkCPULoad();
                
                _d($webUrl->url);
                
                $webUrl->getThumbnails();
            }
        }
    }
}
