<?php

class WebResourceController extends Coda_Controller
{
    public function indexAction()
    {
        // List all resources
        $webResourcesTable = new God_Model_WebResourceTable;
        $webResources = $webResourcesTable->getInstance()
            ->createQuery('wr')
            ->where('sitescan = ?', 1)
            ->orderBy('nextCheck ASC');
        $paginator = new Doctrine_Pager($webResources, $this->_getParam('page',1), 10 );

        $webResources = $paginator->execute();

        $this->view->paginator = $paginator;
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
        $form = new God_Form_WebResource();

        $webResourcesTable = new God_Model_WebResourceTable;
        $webResource = $webResourcesTable->getInstance()->findOneBy('id', $this->_request->getParam('id'));

        $form->populate($webResource->toArray());
        $this->view->form = $form;

        $this->view->webResource = $webResource;

        $curl = new God_Model_Curl();
        $html = $curl->Curl($webResource->sitescanurl);
        $this->view->html = $html;

        $domXPath = new God_Model_DomXPath($html);
        $links = $domXPath->evaluate($webResource->sitescanxpath);

        $this->view->links = $links;
    }
}
