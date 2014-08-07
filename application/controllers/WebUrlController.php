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
            $modelNames = God_Model_ModelNameTable::getInstance()->createQuery('mn')
                ->select('ID')
                ->where('model_id = ?', $this->_request->getParam('modelid'))
                ->execute();
            foreach ($modelNames as $modelName) {
                $modelIds[] = $modelName->ID;
            }
            
            $webUrlQuery
            ->innerJoin('wu.ModelNameWebURL mnwu')
            ->innerJoin('mnwu.modelName mn')
            ->whereIn('mn.model_id', $modelIds);
        }

        $webUrlQuery->andWhere('wu.linked < 0');
        
        $paginator = new Doctrine_Pager($webUrlQuery, $this->_getParam('page', 1), 5);
        $webUrls = $paginator->execute();

        $this->view->paginator = $paginator;
        $this->view->webUrls = $webUrls;
    }

    public function testAction()
    {
        $form = new God_Form_WebResource();

        $webUrlTable = new God_Model_WebURLTable();
        $webUrlQuery = $webUrlTable->getInstance()
            ->createQuery('wu')
            ->innerJoin('wu.webResource wr')
            ->where('id = ?', $this->_request->getParam('id'));
        $webUrl = $webUrlQuery->execute();
        $webUrl = $webUrl[0];

        $form->populate($webUrl->webResource->toArray());

        $this->view->form = $form;

        $this->view->webResource = $webUrl;

        $curl = new God_Model_Curl();
        $html = $curl->Curl($webUrl->url);
        $this->view->html = $html;

        $domXPath = new God_Model_DomXPath($html);
        $links = $domXPath->evaluate($webUrl->webResource->xpathfilter);

        $this->view->links = $links;
    }

    public function renderpath($path) {

        return $this->url.$path;

    }

}