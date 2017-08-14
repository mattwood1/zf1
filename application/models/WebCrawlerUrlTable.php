<?php
class God_Model_WebCrawlerUrlTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_WebCrawlerUrl');
    }

    public function getQuery()
    {
        $webCrawlerUrlQuery = $this->getInstance()
            ->createQuery('wu')
            ->leftJoin('wu.domain wd')
            ->where('(wu.followed < ? or wu.frequency is not null)', God_Model_WebCrawlerUrl::FOLLOWEDTARGET)
            ->andWhere('wu.contenttype like ?', '%text/html%')
            ->andWhere('(wu.date < ? or wu.date is null)', date("Y-m-d H:i:s"))
            ->andWhere('wd.allowed = 1')
            ->orderBy('wu.date DESC, wu.followed DESC')
            ->limit(100);

//        _dexit($webCrawlerUrlQuery);

        return $webCrawlerUrlQuery;
    }

    public function findInsert(God_Model_Curl $curl)
    {
        $url = self::getInstance()->findOneBy('url', $curl->lastUrl());

        if (!$url) {
            $url = God_Model_WebCrawlerUrl::create($curl);
        }

        return $url;
    }
}