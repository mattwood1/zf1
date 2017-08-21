<?php
/**
 * Scheduled to run every minute every day.
 */
class Job_WebCrawler_test extends Job_Abstract
{
    public function run()
    {
        $start = microtime(true);
        $link = 4184894;
        $url = 651890;

        $linkObj = God_Model_WebCrawlerUrlLinkTable::getInstance()->createQuery('wcul')
            ->where('link_id = ?', $link)
            ->andWhere('url_id = ?', $url)
            ->execute();

        _d(microtime(true) - $start);
        _d($linkObj);

        $start = microtime(true);
        $conn = Doctrine_Manager::getInstance()->connection();

        $linkObj = $conn->fetchAssoc(
            'SELECT * FROM webcrawlerUrlLink_ref WHERE link_id = ? AND url_id = ?',
            array($link, $url)
        );

        _d(microtime(true) - $start);
        _d($linkObj);

        exit;
        if (!$linkRef) {
            $table = God_Model_WebCrawlerUrlLinkTable::getInstance();
            $conn->insert($table, array('link_id' => $link->id, 'url_id' => $url->id));
        }

    }
}