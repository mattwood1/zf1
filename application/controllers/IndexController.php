<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $query = Doctrine_Core::getTable('God_Model_Model')
                        ->createQuery('m')
                        ->innerJoin('m.names n')
                        ->innerJoin('m.photosets p')

                        ->where('m.active = ?', 1)
                        ->andWhere('m.ranking >= ?', 0)

                        ->andWhere('n.default = ?', 1)

                        ->andWhere('p.active = ?', 1)
                        ->andWhere('p.manual_thumbnail = ?', 1)
                        ->orderBy('m.ranking desc, p.name desc');

        if ($this->_request->isPost()) {
            $query->andWhere('n.name like ?', '%' . $this->_request->getParam('search') . '%') ;
        }

        $paginator = new Doctrine_Pager($query, $this->_getParam('page',1), 24 );

        $models = $paginator->execute();

        $this->view->paginator = $paginator;
        $this->view->models = $models;

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

