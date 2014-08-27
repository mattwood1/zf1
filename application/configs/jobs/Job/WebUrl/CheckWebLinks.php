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
            ->where('linked = ?', God_Model_WebURLTable::LINK_NOT_LINKED)
            ->limit(800)
        ;
        $webUrls = $webUrlsQuery->execute();

        foreach ($webUrls as $webUrl) {
            if (preg_match("~(\/search\/)~i", $webUrl->url)) {
                $webUrl->action = God_Model_WebURLTable::ACTION_SEARCH;
                $webUrl->linked = -10;
            }
            if (preg_match("~(%3Ffrom%3D)~i", $webUrl->url)) {
                $webUrl->action = God_Model_WebURLTable::ACTION_FROM;
                $webUrl->linked = -10;
            }

            // These will need following up.
            if ($webUrl->action == God_Model_WebURLTable::ACTION_DISCARDED
                || $webUrl->action == God_Model_WebURLTable::ACTION_NEW_URL
                || $webUrl->action == God_Model_WebURLTable::ACTION_GET_THUMBNAILS
                || $webUrl->action == God_Model_WebURLTable::ACTION_GOT_THUMBNAILS
                || $webUrl->action == God_Model_WebURLTable::ACTION_THUMBNAIL_ISSUE
                || ($webUrl->action == God_Model_WebURLTable::PARENT_LINKED
                    && $webUrl->linked == God_Model_WebURLTable::LINK_NOT_LINKED)
            ){
                $webUrl->linked = God_Model_WebURLTable::LINK_TO_BE_LINKED;
            }

            if ($webUrl->action == God_Model_WebURLTable::ACTION_READY_TO_DOWNLOAD) {
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
                $webUrl->linked = God_Model_WebURLTable::LINK_TO_BE_LINKED;
            }

            $webUrl->save();
        }
    }
}