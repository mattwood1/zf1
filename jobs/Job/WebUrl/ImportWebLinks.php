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
            ->limit(800);
        $webLinks = $webLinkQuery->execute();

        foreach ($webLinks as $webLink) {
//var_dump($webLink->id, $webLink->url);
            // Find WebUrl by URL
            $webUrlTable = new God_Model_WebURLTable;
            $webUrlTable->getURL($webLink->url);
            $webUrl = $webUrlTable->getQuery()->execute();

//var_dump($webUrl[0]->id, $webUrl[0]->url);

            // If not found create it
            if (!$webUrl[0]->url) {
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
//var_dump('Created');
                $webLink->delete();
//var_dump('Deleted 1');
            } elseif (preg_match("~^" . preg_quote($webUrl[0]->url) . "$~i", addslashes($webLink->url))) { // else check it and delete.
                $webLink->delete();
//var_dump('Deleted 2');
            }
//var_dump('Match -> ', preg_match("~^" . preg_quote($webUrl[0]->url) . "$~i", addslashes($webLink->url)));
        }

    }
}