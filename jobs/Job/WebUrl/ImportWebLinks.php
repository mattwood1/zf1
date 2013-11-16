<?php
/**
 * This job is responsible for Importing WebLinks into WebUrls for better management.
 *
 * Scheduled to run every minute every day.
 */
class Job_WebUrl_ImportWebLinks extends Job_Abstract
{
    public function run()
    {
        $webLinkTable = new God_Model_WebLinkTable;
        $webLinkQuery = $webLinkTable->getInstance()
            ->createQuery('wl')
            ->limit(500);
        $webLinks = $webLinkQuery->execute();

        foreach ($webLinks as $webLink) {
            // Find WebUrl by URL
            $webUrlTable = new God_Model_WebURLTable;
            $webUrlTable->getURL($webLink->url);
            $webUrl = $webUrlTable->getQuery()->execute();

            //var_dump($webUrl->toArray());

            // If not found create it
            if (!$webUrl->toArray()) {
                $webUrl = new God_Model_WebURL;

                $data = array(
                        'webResourceId' => $webLink->webresourceid,
                        'url' => $webLink->url,
                        'httpStatusCode' => $webLink->statusCode,
                        'action' => $webLink->action,
                        'thumbnails' => $webLink->thumbnails,
                        'links' => $webLink->links,
                        'images' => $webLink->images,
                        'dateCreated' => $webLink->dateCreated
                );
                $webUrl = Doctrine_Core::getTable('God_Model_WebURL')->create($data);
                $webUrl->save();
                $webLink->delete();
            } elseif ($webUrl[0]->url == $webLink->url) { // else check it and delete.
                $webLink->delete();
            }
        }

    }
}