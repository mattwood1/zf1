<?php
class God_Model_WebCrawlerUrlTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_WebCrawlerUrl');
    }

    public function findInsert(God_Model_Curl $curl)
    {
        $url = self::getInstance()->findOneBy('url', $curl->lastUrl());

        if (!$url) {
            God_Model_WebCrawlerUrl::create($curl);
        }

        return $url;
    }
}