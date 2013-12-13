<?php

class WebResourceController extends Coda_Controller
{
    public function indexAction()
    {
        // List all resources
        $webResourcesTable = new God_Model_WebResourceTable;
        $webResources = $webResourcesTable->getInstance()->findAll();

        $this->view->webResources = $webResources;
    }

    public function editAction()
    {
        $webResourcesTable = new God_Model_WebResourceTable;
        $webResource = $webResourcesTable->getInstance()->findOneBy('id', $this->_request->getParam('id'));

        $this->view->webResource = $webResource;
    }

    public function testAction()
    {
        $webResourcesTable = new God_Model_WebResourceTable;
        $webResource = $webResourcesTable->getInstance()->findOneBy('id', $this->_request->getParam('id'));
        $this->view->webResource = $webResource;

        $curl = new God_Model_Curl();
        $html = $curl->Curl($webResource->sitescanurl);
        $this->view->html = $html;

        $domXPath = new God_Model_DomXPath($html);
        $links = $domXPath->evaluate($webResource->sitescanxpath);

        $this->view->links = $links;
    }
}