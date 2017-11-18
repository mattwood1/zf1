<?php
class God_Model_WebCrawlerUrlPhotosetsTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_Base_WebCrawlerUrlPhotosets');
    }
}