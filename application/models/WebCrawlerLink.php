<?php
class God_Model_WebCrawlerLink extends God_Model_Base_WebCrawlerLink
{
    const PRIORTIY_HIGH = 1;
    const PRIORITY_MED  = 50;
    const PRIORITY_LOW  = 99;

    public static function create($linkString)
    {
        $link = new God_Model_WebCrawlerLink();
        $link->link = $linkString;
        $link->priority = 0;

        $link->save();

        return $link;
    }
}