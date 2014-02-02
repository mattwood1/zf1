<?php

class ModelController extends Coda_Controller
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function viewAction()
    {
        // add body
        $query = Doctrine_Core::getTable('God_Model_Photoset')
            ->createQuery('p')
            ->innerJoin('p.model m')
            ->innerJoin('m.names n')

            ->where('m.ID = ?', $this->_request->getParam('id'))
            ->andWhere('m.active = ?', 1)
            ->andWhere('n.default = ?', 1)
            ->andWhere('p.active = ?', 1)
            ->orderBy('p.name asc');

        $paginator = new Doctrine_Pager($query, $this->_getParam('page',1), 18 );

        $photosets = $paginator->execute();

        $this->view->paginator = $paginator;
        $this->view->photosets = $photosets;
    }

    public function addAction()
    {
        // add body
        $form = new God_Form_Model();
        $form->submit->setLabel('Add');
        $this->view->form = $form;
    }

    public function editAction()
    {
        // edit body
        $form = new God_Form_Model();
        $form->submit->setLabel('Save');
        $this->view->form = $form;

        if ($this->getRequest()->isPost()) {
            $formData = $this->getRequest()->getPost();
            if ($form->isValid($formData)) {
                $id = (int)$form->getValue('id');
                $artist = $form->getValue('name');
                /*
                 * TODO: Add in the path and uri
                 */
                $models = new God_Model_Model();
                $models->updateModel($id, $name, $path, $uri);
                $this->_helper->redirector('view');
            } else {
                $form->populate($formData);
            }
        }

        else {
            $model = Doctrine_Core::getTable('God_Model_Model')
                ->createQuery('m')
                ->innerJoin('m.names n')
                ->where('m.ID = ?', $this->_request->getParam('id'));
            $model = $model->execute()->toArray();

            //echo '<pre>';var_dump($model);echo '</pre>';

            $form->populate($model[0]);
        }

        $model = Doctrine_Core::getTable('God_Model_Model')->findOneBy('ID', $this->_request->getParam('id'));

        $this->view->model = $model;
    }

    public function rankingAction()
    {
        if ($this->_request->isPost()) {
            $model = Doctrine_Core::getTable('God_Model_Model')->findOneBy('ID', $this->_request->getParam('model_id'));
            $model->ranking++;
            $model->search = (bool)$this->_request->getParam('search');
            $model->save();
        }

        $modelTable = new God_Model_ModelTable;

        // Get model ranking stats
        $rankingStats = $modelTable->getRankingStats(2, true);

        // Choose a random model stat
        $rankingStatsKey = array_rand($rankingStats, 1);

        // Get models where ranking = the chosen stat
        $models = $modelTable->getModelsByRanking($rankingStatsKey);

        $this->view->models = $models;
        $this->view->modelKeys = array_rand($models->toArray(), 2);
    }

    public function statsAction()
    {
        $modelTable = new God_Model_ModelTable;
        $this->view->rankings = $modelTable->getRankingStats();
    }

    /*
     * Potentially redundant
     */
    public function webLinkAction()
    {
        if ($this->_request->isPost()) {
            foreach ($this->_request->getParam('weblink') as $id => $action) {
                $weblink = Doctrine_Core::getTable('God_Model_WebLink')->findBy('id', $id);
                $weblink->set('action', $action);
                //$weblink->save();
                echo '<pre>'; var_dump($action, $weblink); echo '</pre>';
            }
        }

        $query = Doctrine_Query::create()
            ->select('l.*')
            ->from('God_Model_WebLink l')
            ->innerJoin('l.model m')
            ->innerJoin('m.names n')

            ->where('action = ?', $this->_request->getParam('key'))
            ->andWhere('m.ID = ?', $this->_request->getParam('id'))
            ->andWhere('n.default = ?', 1)

            ->limit(5);

            //->groupBy('l.model_id')
            //->orderBy('m.ranking desc')
        ;
        $weblinks = $query->execute();
        $this->view->webLinks = $weblinks;
        /*
        echo '<pre>';
        var_dump($weblinks->toArray());
        exit;
        */
    }

    public function deleteAction()
    {
        // delete body
    }

}

