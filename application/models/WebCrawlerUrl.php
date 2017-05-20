<?php
class God_Model_WebCrawlerUrl extends God_Model_Base_WebCrawlerUrl
{
    public static function create(God_Model_Curl $curl)
    {
        $domainTable = new God_Model_WebCrawlerDomainTable();
        $domain = $domainTable->findInsert($curl);

        $url = new God_Model_WebCrawlerUrl();
        $url->url = $curl->lastUrl();
        $url->contenttype = $curl->contentType();
        $url->contentlength = $curl->contentLength();
        $url->statuscode = $curl->statusCode();
        $url->domain_id = $domain->id;

        $url->save();

        God_Model_WebCrawlerUrlModelName::createLink($url);

        return $url;
    }
}