<?php

class IndexController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $modelTable = new God_Model_ModelTable;

        $modelTable->setOrder($this->_getParam('order', 'ranking'));

        $modelTable->getModels();

        if ($this->_request->getParam('search')) {
            $this->keyword = $this->_request->getParam('search');
            $modelTable->setSearch($this->keyword);
            $this->view->keyword = $this->keyword;
        }

        $paginator = new Doctrine_Pager($modelTable->getQuery(), $this->_getParam('page', 1), 18);
        $models = $paginator->execute();
        
        $this->view->paginator = $paginator;
        $this->view->models = $models;
    }

    public function searchAction()
    {
        if ($this->_request->isPost()) {
            $this->gotoRoute(array('action' => 'index', 'search' => $this->_request->getParam('keyword')));
        }
        $this->gotoRoute(array('action' => 'index'));
    }

    public function addAction()
    {
        // add body
    }

    public function editAction()
    {
        // edit body
    }

    public function deleteAction()
    {
        // delete body
    }
}

