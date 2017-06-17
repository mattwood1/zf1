<?php

class WebcrawlerDomainController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $webCrawlerDomains = God_Model_WebCrawlerDomainTable::getInstance()
            ->createQuery('wcd')
            ->leftJoin('wcd.urls as urls')
            ->where('wcd.allowed = ?', 1)
            ->andWhere('urls.frequency is not null')
            ->orderBy('urls.date asc')
            ->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $this->view->domains = $webCrawlerDomains;
    }
}