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
                        ->leftJoin('m.photosets p')

                        ->where('m.active = ?', 1)
                        ->andWhere('m.ranking >= ?', 0)

                        ->andWhere('n.default = ?', 1)

                        ->andWhere('p.active = ?', 1)
                        ->andWhere('p.manual_thumbnail = ?', 1);

        // Search
        if ($this->_request->getParam('search')) {
            $query->andWhere('n.name like ?', '%' . $this->_request->getParam('search') . '%') ;
        }

        // Ordering
        if ($this->_request->getParam('order')) {
            switch ($this->_request->getParam('order')) {
                case 'ranking':
                    $query->orderBy('m.ranking desc, p.name desc');
                    break;
                case 'name':
                    $query->orderBy('n.name asc, p.name desc');
                    break;
            }
        } else {
            $query->orderBy('m.ranking desc, p.name desc');
        }

        $paginator = new Doctrine_Pager($query, $this->_getParam('page',1), 18 );

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

