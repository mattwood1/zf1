<?php

class WebcrawlerUrlController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $thumbnailSize = 0;

        $webUrlTable = new God_Model_WebCrawlerUrlTable();

        $webUrlQuery = $webUrlTable->getDisplayQuery();

        if ($this->_request->getParam('modelid')) {

            $this->view->model = God_Model_ModelTable::getInstance()->find($this->_request->getParam('modelid'));

            $webUrlQuery->innerJoin('mn.model m');
            $webUrlQuery->andWhere('m.id = ?', $this->_request->getParam('modelid'));
        }

        if ($this->_request->getParam('domainid')) {

            $webUrlQuery->andWhere('domain.id = ?', $this->_request->getParam('domainid'));
            $this->view->domain = God_Model_WebCrawlerDomainTable::getInstance()->find($this->_request->getParam('domainid'));
        }

//        $webUrlQuery->orderBy('wcu.id DESC');

//        ini_set('xdebug.var_display_max_data', -1);
//        _d($webUrlQuery);

        $paginator = new Doctrine_Pager($webUrlQuery, $this->_getParam('page', 1), 5);
        $webUrls = $paginator->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $this->view->paginator = $paginator;
        $this->view->webUrls = $webUrls;
    }
}
