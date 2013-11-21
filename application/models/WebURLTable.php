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

    public function insertLink($url, $webReourceId) {
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

            // Link Model Name
            $modelNameTable = new God_Model_ModelNameTable;
            $modelNames = $modelNameTable->getInstance()->findAll();

            foreach ($modelNames as $modelName) {
                //var_dump(($modelName->name));
                $name = str_replace(" ", "[\s\-\_]", $modelName->name);

                if (preg_match("~(" . $name . ")~i", $webUrl->url)) {
                    $webUrl->linked = -5;                // Name Match found
                    $webUrl->action = God_Model_WebURLTable::GET_THUMBNAILS; // Set to get thumbs
                    $modelNamewebUrl = Doctrine_Core::getTable('God_Model_ModelNameWebURL')->create(array(
                            'model_name_id' => $modelName->ID,
                            'webUrl_id'     => $webUrl->id
                    ));
                    $modelNamewebUrl->save();
                }
            }

            $webUrl->save();
        }
    }
}