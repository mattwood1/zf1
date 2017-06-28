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

        return $url;
    }

    public function linkModelName()
    {
        if (stripos($this->contenttype, "text/html") === false || $this->statuscode != 200) return;

        $modelNames = God_Model_ModelNameTable::getByUrl($this->url);

        God_Model_WebCrawlerUrlModelName::createLink($this, $modelNames);
        
    }
}
