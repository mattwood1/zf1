<?php
class God_Model_WebCrawlerLinkTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_WebCrawlerLink');
    }

    public static function findInsert($linkString, God_Model_WebCrawlerUrl $url)
    {
        checkCPULoad(1.5);

        $link = self::getInstance()->findOneBy('link', $linkString);

        if (!$link) {
            $link = God_Model_WebCrawlerLink::create($linkString);
        }

        $url->modelnamelinks;

        if ($url->modelnamelinks) {
            $link->priority = God_Model_WebCrawlerLink::PRIORTIY_HIGH;
        }
        else {
            $link->priority = God_Model_WebCrawlerLink::PRIORITY_LOW;
        }

        $link->save();

        God_Model_WebCrawlerUrlLinkTable::findInsert($link, $url);
    }
}