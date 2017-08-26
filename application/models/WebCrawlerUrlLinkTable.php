<?php
class God_Model_WebCrawlerUrlLinkTable extends Doctrine_Record
{
    public static function getInstance()
    {
        return Doctrine_Core::getTable('God_Model_WebCrawlerUrlLink');
    }

    public function findInsert(God_Model_WebCrawlerLink $link, God_Model_WebCrawlerUrl $url)
    {
        // PDO Version
        $conn = Doctrine_Manager::getInstance()->connection();
        $linkRef = $conn->fetchArray(
            'SELECT * FROM webcrawlerUrlLink_ref WHERE link_id = ? AND url_id = ?',
            array($link->id, $url->id)
        );

        if (!$linkRef) {
            self::Insert($link->id, $url->id);
        }

    }

    public static function Insert($link_id, $url_id)
    {
        $conn = Doctrine_Manager::getInstance()->connection();
        $conn->insert(
            God_Model_WebCrawlerUrlLinkTable::getInstance(),
            array('link_id' => $link_id, 'url_id' => $url_id)
        );
    }
}