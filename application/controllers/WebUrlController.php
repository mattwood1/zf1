<?php

class WebUrlController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $webUrlQuery = God_Model_WebURLTable::getInstance()
            ->createQuery('wu')
            ->orderBy('wu.dateCreated DESC')
            ->leftJoin('wu.ModelNameWebURL mnwu')
            ->leftJoin('mnwu.modelName mn');

        if ($this->_request->getParam('webresourceid')) {
            $this->view->webresource = God_Model_WebResourceTable::getInstance()->find($this->_request->getParam('webresourceid'));
            $webUrlQuery->where('wu.webResourceId = ?', $this->_request->getParam('webresourceid'));
        }

        if ($this->_request->getParam('modelid')) {
            $this->view->model = God_Model_ModelTable::getInstance()->find($this->_request->getParam('modelid'));
            $modelNames = God_Model_ModelNameTable::getInstance()->createQuery('mn')
                ->select('ID')
                ->where('model_id = ?', $this->_request->getParam('modelid'))
                ->execute();
            foreach ($modelNames as $modelName) {
                $modelIds[] = $modelName->ID;
            }
            
            $webUrlQuery
            ->whereIn('mn.id', $modelIds);
        }

        $webUrlQuery->andWhere('wu.linked < 0');
        
        $paginator = new Doctrine_Pager($webUrlQuery, $this->_getParam('page', 1), 5);
        $webUrls = $paginator->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

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