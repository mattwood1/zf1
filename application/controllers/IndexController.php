<?php

class IndexController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $ModelTable = new God_Model_ModelTable;

        $ModelTable->getModels();

        if ($this->_request->getParam('search')) {
            $ModelTable->setSearch($this->_request->getParam('search'));
        }

        if ($this->_request->getParam('order')) {
            $ModelTable->setOrder($this->_request->getParam('order'));
        }

        $paginator = new Doctrine_Pager($ModelTable->getQuery(), $this->_request->getParam('page'), 18);
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

