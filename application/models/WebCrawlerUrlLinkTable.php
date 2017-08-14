<?php
class God_Model_WebCrawlerUrlLinkTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_WebCrawlerUrlLink');
    }

    public function findInsert(God_Model_WebCrawlerLink $link, God_Model_WebCrawlerUrl $url)
    {
        $urlLinkQuery = God_Model_WebCrawlerUrlLinkTable::getInstance()->createQuery()
            ->where('link_id = ?', $link->id)
            ->andWhere('url_id = ?', $url->id);
        $urlLink = $urlLinkQuery->execute();

        if (!$urlLink->count()) {
            $urlLink = new God_Model_WebCrawlerUrlLink();
            $urlLink->link_id = $link->id;
            $urlLink->url_id = $url->id;

            $urlLink->save();
        }

    }
}