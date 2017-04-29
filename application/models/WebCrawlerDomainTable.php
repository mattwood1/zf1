<?php
class God_Model_WebCrawlerDomainTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_WebCrawlerDomain');
    }

    public function findInsert(God_Model_Curl $curl)
    {
        $parsedUrl = parse_url($curl->lastUrl());

        preg_match("~(\w+\.(?:\w{2,3}|\w{2,3}\.\w{2,3}))$~", $parsedUrl['host'], $domainName);

        if (array_key_exists('1', $domainName)) {
            $domainName = $domainName[1];
        }

        $domain = self::getInstance()->findOneBy('domain', $domainName);

        if (!$domain) {
            $domain = new God_Model_WebCrawlerDomain();
            $domain->domain = $domainName;
            $domain->save();
        }

        return $domain;

    }
}