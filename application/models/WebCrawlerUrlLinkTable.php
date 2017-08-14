<?php
class God_Model_WebCrawlerUrlLinkTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_WebCrawlerUrlLink');
    }

    public function findInsert(God_Model_Curl $curl)
    {

    }
}