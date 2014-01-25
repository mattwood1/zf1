<?php

class WebUrlController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $webUrlTable = new God_Model_WebURLTable();
        $webUrlQuery = $webUrlTable->getInstance()
            ->createQuery('wu')
            ->orderBy('wu.dateCreated DESC');

        if ($this->_request->getParam('webresourceid')) {
            $webUrlQuery->where('wu.webResourceId = ?', $this->_request->getParam('webresourceid'));
        }

        if ($this->_request->getParam('modelid')) {
            $webUrlQuery
            ->innerJoin('wu.ModelNameWebURL mnwu')
            ->innerJoin('mnwu.modelName mn')
            ->where('mn.model_id = ?', $this->_request->getParam('modelid'));
        }

        $paginator = new Doctrine_Pager($webUrlQuery, $this->_getParam('page', 1), 5);
        $webUrls = $paginator->execute();

        $this->view->paginator = $paginator;
        $this->view->webUrls = $webUrls;
    }

    public function testAction()
    {
        $webUrlTable = new God_Model_WebURLTable();
        $webUrlQuery = $webUrlTable->getInstance()
            ->createQuery('wu')
            ->innerJoin('wu.webResource wr')
            ->where('id = ?', $this->_request->getParam('id'));
        $webUrl = $webUrlQuery->execute();
        $webUrl = $webUrl[0];


        $this->view->webResource = $webUrl;

        $curl = new God_Model_Curl();
        $html = $curl->Curl($webUrl->url);
        $this->view->html = $html;

        $domXPath = new God_Model_DomXPath($html);
        $links = $domXPath->evaluate($webUrl->webResource->xpathfilter);

        $this->view->links = $links;
    }

    public function cacheAction()
    {
        $cache = Zend_Cache::factory('Core', 'Memcached');

        $test = $cache->load('MyTest');

        $cache->save('My test text', 'MyTest');

        _d($cache, $test);

        exit;
    }

    public function renderpath($path) {

        return $this->url.$path;

    }

}