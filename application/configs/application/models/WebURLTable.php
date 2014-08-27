<?php
class God_Model_WebURLTable extends Doctrine_Record
{
    protected $_query;

    // Discarded links need following up

    //const FROM              = -7; // PHASE OUT THESE CONSTANTS FOR CLARITY
    //const SEARCH            = -6;
    const PARENT_LINKED     = -5;
    //const DISCARDED         = -1;  // Link has been manually discarded
    //const NEW_URL           = 0;
    //const GET_THUMBNAILS    = 1;
    //const GOT_THUMBNAILS    = 2;
    //const THUMBNAIL_ISSUE   = 3;
    //const READY_TO_DOWNLOAD = 6;

    // Link Codes
    const LINK_NOT_LINKED   = 0;
    const LINK_TO_BE_LINKED = -1;
    const LINK_ATTEMPTED    = -2;
    const LINK_FOUND        = -5;

    // Action Codes
    const ACTION_FROM              = -7;
    const ACTION_SEARCH            = -6;
    const ACTION_DISCARDED         = -1;
    const ACTION_NEW_URL           = 0;
    const ACTION_GET_THUMBNAILS    = 1;
    const ACTION_GOT_THUMBNAILS    = 2;
    const ACTION_THUMBNAIL_ISSUE   = 3;
    const ACTION_GET_IMAGES        = 4;
    const ACTION_GOT_IMAGES        = 5;
    const ACTION_READY_TO_DOWNLOAD = 6;
    const ACTION_DOWNLOADED        = 10;

    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_WebURL');
    }

    public function getURL($url)
    {
        $this->_query = $this->getInstance()
            ->createQuery('wu')
            ->where('wu.url = ?', $url);
    }

    public function getQuery()
    {
        return $this->_query;
    }

    public function insertLink($url, God_Model_WebResource $webResource) {
        $urlQuery = $this->getURL($url);
        $urlData = $this->getQuery()->execute();

        if (!$urlData->toArray()) {
            $webUrl = Doctrine_Core::getTable('God_Model_WebURL')->create(array(
                    'webResourceId' => $webResource->id,
                    'url' => $url,
                    'httpStatusCode' => 0,
                    'action' => God_Model_WebURLTable::ACTION_NEW_URL,
                    'thumbnails' => null,
                    'links' => null,
                    'images' => null,
                    'linked' => -2,
                    'dateCreated' => date(date("Y-m-d H:i:s"))
            ));
            $webUrl->save();

            $webUrl->linkModelNameToUrl();

            $webUrl->save();

            $webResource->lastUpdated = date("Y-m-d H:i:s");
            $webResource->save();

            $urlData = $webUrl;
        } else {
            return $urlData[0];
        }

        return $urlData;
    }
}
