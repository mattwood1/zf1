<?php
class God_Model_WebCrawlerLinkTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_WebCrawlerLink');
    }

    public static function findInsert($linkString, God_Model_WebCrawlerUrl $url)
    {
//        checkCPULoad(God_Model_WebCrawlerUrl::CPULOAD);

        if (strpos($linkString, '@') !== false) {
            return;
        }

        if (strlen($linkString) > 1000) {
            return;
        }

        $url->modelnamelinks;

        $link = self::getInstance()->findOneBy('link', $linkString);

        if (!$link) {
//            _d('New Link');
            $link = God_Model_WebCrawlerLink::create($linkString);
//            _d($url->url);
        }
        $link->save();

        self::updateLinkPriority($link, $url);

        if ($url->modelnamelinks->count() > 0 && $link->url_id == 0) {
//            _d($link);
        }

        God_Model_WebCrawlerUrlLinkTable::findInsert($link, $url);
    }

    public static function updateLinkPriority(God_Model_WebCrawlerLink $link, God_Model_WebCrawlerUrl $url)
    {

        if ($url->modelnamelinks->count() > 0 && $link->url_id == 0 && $link->priority != God_Model_WebCrawlerLink::PRIORTIY_HIGH) {
            $link->priority = God_Model_WebCrawlerLink::PRIORTIY_HIGH;
        }
        elseif ($url->modelnamelinks->count() == 0 && $link->url_id == 0 && $link->priority != God_Model_WebCrawlerLink::PRIORITY_LOW) {
            $link->priority = God_Model_WebCrawlerLink::PRIORITY_LOW;
        }

        // Lower Forum links
        $parseLink = parse_url($link->link);
        if (array_key_exists('host', $parseLink) && strpos($parseLink['host'], 'forum') !== false) {
            $link->priority = God_Model_WebCrawlerLink::PRIORITY_LOW;
        }

        $link->save();
    }

    public static function updateLinksPriority(array $linkIds, $priority)
    {
        $conn = Doctrine_Manager::getInstance()->connection();

        $conn->execute('UPDATE webcrawlerLinks SET priority = ' . $priority .' WHERE id IN (' . implode(',', $linkIds) . ') AND url_id = 0');
    }
}