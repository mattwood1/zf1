<?php
/**
 * This job is responsible for checking webUrls
 * - Correctly updates the action code and linked params.
 *
 * Scheduled to run every minute every day.
 */
class Job_WebUrl_CheckWebLinks extends Job_Abstract
{
    public function run()
    {
        $webUrlsTable = new God_Model_WebURLTable;
        $webUrlsQuery = $webUrlsTable->getInstance()
            ->createQuery('wu')
            ->where('linked = ?', 0)
            ->limit(800)
        ;
        $webUrls = $webUrlsQuery->execute();

        foreach ($webUrls as $webUrl) {
            if (preg_match("~(\/search\/)~i", $webUrl->url)) {
                $webUrl->action = God_Model_WebURLTable::SEARCH;
                $webUrl->linked = -10;
            }
            if (preg_match("~(%3Ffrom%3D)~i", $webUrl->url)) {
                $webUrl->action = God_Model_WebURLTable::FROM;
                $webUrl->linked = -10;
            }

            // These will need following up.
            if ($webUrl->action == God_Model_WebURLTable::DISCARDED
                || $webUrl->action == God_Model_WebURLTable::NEW_URL
                || $webUrl->action == God_Model_WebURLTable::GOT_THUMBNAILS
            ){
                $webUrl->linked = -1;
            }

            if ($webUrl->action == God_Model_WebURLTable::THUMBNAIL_ISSUE){
                $webUrl->linked = -3;
            }

            if ($webUrl->action == God_Model_WebURLTable::PARENT_LINKED && $webUrl->linked == 0){
                $webUrl->linked = -1;
            }

            if ($webUrl->action == God_Model_WebURLTable::READY_TO_DOWNLOAD) {
                $links = unserialize($webUrl->links);
                if ($links) {
                    $links = array_filter($links); // Remove empty entries
                    foreach ($links as $link) {
                        $webUrlsLinkTable = new God_Model_WebURLTable;
                        $webUrlsLinkQuery = $webUrlsTable->getInstance()
                            ->createQuery('wul')
                            ->where('url LIKE ?', '%'.$link)
                            ->andWhere('webResourceId = ?', $webUrl->webResourceId);
                        $webUrlsLinks = $webUrlsLinkQuery->execute();
                        foreach ($webUrlsLinks as $webUrlsLink) {
                            if ($webUrlsLink->toArray()) {
                                $webUrlsLink->action = God_Model_WebURLTable::PARENT_LINKED;
                                $webUrlsLink->linked = $webUrl->id;
                                $webUrlsLink->save();
                            }
                        }
                    }
                }
                $webUrl->linked = -1;
            }

            $webUrl->save();
        }
    }
}