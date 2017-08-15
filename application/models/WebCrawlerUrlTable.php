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
            ->where('(wu.followed > ? or wu.frequency is not null)', God_Model_WebCrawlerUrl::FOLLOWEDTARGET)
            ->andWhere('wu.contenttype like ?', '%text/html%')
            ->andWhere('(wu.date < ? or wu.date is null)', date("Y-m-d H:i:s"))
            ->andWhere('wd.allowed = 1')
            ->orderBy('wu.date DESC, wu.followed DESC, wu.id DESC')
            ->limit(100);

//        _dexit($webCrawlerUrlQuery);

        return $webCrawlerUrlQuery;
    }

    public function getDisplayQuery()
    {
        $webUrlQuery = $this->getInstance()
            ->createQuery('wcu')
            ->leftJoin('wcu.linkref as linkref1')
            ->leftJoin('linkref1.link as link1')
            ->leftJoin('link1.url as wcu1')

            ->leftJoin('wcu1.linkref as linkref2')
            ->leftJoin('linkref2.link as link2')
            ->leftJoin('link2.url as wcu2')

            ->leftJoin('wcu.modelnamelinks mnl')
            ->leftJoin('mnl.modelName mn')

            ->leftJoin('wcu.domain domain')

            ->andWhere('
            (
                (    domain.link_depth = 1
                 and wcu1.contenttype = "image/jpeg"
                 and wcu1.pixels > domain.minSize
                 and wcu2.contenttype is null 
                 and wcu2.contentlength is null)
            OR  (
                     domain.link_depth = 2
                 and wcu1.contenttype like "text/html%"
                 and wcu.domain_id = wcu1.domain_id 
                 and wcu2.contenttype = "image/jpeg"
                 and wcu2.pixels > domain.minSize
                )
            )');

        return $webUrlQuery;
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