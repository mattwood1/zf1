<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    	$models = new Application_Model_DbTable_Models();

    	$order = $this->_getParam('order', 'ranking_desc');
    	$order = str_replace("_", " ", $order);

    	$result = $models->fetchAll('`active` = 1 AND `ranking` >= 0',$order);
	    $page=$this->_getParam('page',1);
	    $paginator = Zend_Paginator::factory($result);
	    $paginator->setItemCountPerPage(24);
	    $paginator->setDefaultPageRange(5);
	    $paginator->setCurrentPageNumber($page);

    	$this->view->paginator = $paginator;
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

