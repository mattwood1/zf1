<?php
class God_Model_WebURLTable extends Doctrine_Record
{
    protected $_query;

    // Discarded links need following up

    const FROM              = -7;
    const SEARCH            = -6;
    const PARENT_LINKED     = -5;
    const DISCARDED         = -1;  // Link has been manually discarded
    const NEW_URL           = 0;
    const GET_THUMBNAILS    = 1;
    const GOT_THUMBNAILS    = 2;
    const THUMBNAIL_ISSUE   = 3;
    const READY_TO_DOWNLOAD = 6;

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

    public function insertLink($url, $webReourceId = null) {
        $urlQuery = $this->getURL($url);
        $urlData = $this->getQuery()->execute();

        if (!$urlData->toArray()) {
            $webUrl = Doctrine_Core::getTable('God_Model_WebURL')->create(array(
                    'webResourceId' => $webReourceId,
                    'url' => $url,
                    'httpStatusCode' => 0,
                    'action' => God_Model_WebURLTable::NEW_URL,
                    'thumbnails' => null,
                    'links' => null,
                    'images' => null,
                    'linked' => -2,
                    'dateCreated' => date(date("Y-m-d H:i:s"))
            ));
            $webUrl->save();

            $webUrl->linkModelNameToUrl();

            $webUrl->save();
        }
    }
}