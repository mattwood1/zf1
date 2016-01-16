<?php
/**
 * This job is responsible for Importing WebLinks into WebUrls for better management.
 *
 * Scheduled to run every minute every day.
 */
class Job_WebUrl_LinkModelNames extends Job_Abstract
{
    public function run()
    {
        $webUrlsTable = new God_Model_WebURLTable;
        $webUrlsQuery = $webUrlsTable->getInstance()
            ->createQuery('wu')
            ->where('linked = ?', God_Model_WebURLTable::LINK_TO_BE_LINKED)
            ->limit(1500);
        $webUrls = $webUrlsQuery->execute();
        
        foreach ($webUrls as $webUrl) {
            
            $webUrl->linked = God_Model_WebURLTable::LINK_ATTEMPTED; // Processed by this script

            $webUrl->linkModelNameToUrl();

            $webUrl->save();

        }
    }
}