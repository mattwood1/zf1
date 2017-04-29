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

        $domainTable = new God_Model_WebCrawlerDomainTable();
        $domain = $domainTable->findInsert($curl);

        if (!$url) {
            $url = new God_Model_WebCrawlerUrl();
            $url->url = $curl->lastUrl();
            $url->contenttype = $curl->contentType();
            $url->contentlength = $curl->contentLength();
            $url->statuscode = $curl->statusCode();
            $url->domain_id = $domain->id;

            $url->save();

        }

        return $url;
    }
}