<?php

class ModelController extends Coda_Controller
{
    protected $modelSession;

    public function init()
    {
        $this->modelSession = new Zend_Session_Namespace(get_class());
        /* Initialize action controller here */
    }

    public function viewAction()
    {
        $model = God_Model_ModelTable::getInstance()->find($this->_request->getParam('id'));
        
        // TODO: It would be good to have a function $model->getPhotosets with pagination
        // Needs to return a query instance.
        
//         add body
        $query = Doctrine_Core::getTable('God_Model_Photoset')
            ->createQuery('p')
            ->leftJoin('p.model m')
            ->innerJoin('m.names n')

            ->where('m.ID = ?', $this->_request->getParam('id'))
            ->andWhere('m.active = ?', 1)
            ->andWhere('n.default = ?', 1)
            ->andWhere('p.active = ?', 1)
            ->orderBy('p.id desc');
        
        $paginator = new Doctrine_Pager($query, $this->_getParam('page',1), 18 );

        $photosets = $paginator->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

        $this->view->paginator = $paginator;
        $this->view->model = $model;
        $this->view->photosets = $photosets;
    }

    public function addAction()
    {
        $form = new God_Form_AddModel();
        
        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            $modelTable = new God_Model_ModelTable();
            $modelTable->addModel($form->getValue('name'));
        }
        
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
        $model = null;
        if ($this->_request->isPost()) {
            $model = Doctrine_Core::getTable('God_Model_Model')->findOneBy('ID', $this->_request->getParam('model_id'));

            // Check the ranking value to prevent mis clicks and multi clicks
            if ($model->ranking == $this->_request->getParam('model_ranking')) {
                unset($this->modelSession->ranking[$model->ranking]);
                
                $model->ranking++;
                $model->rankDate = date("Y-m-d h:i:s", mktime());
                $model->search = (bool)$this->_request->getParam('search');
                $model->save();
                
                $this->modelSession->ranking[$model->ranking] = $model->ID;
            }
        }

        $modelRanking = new God_Model_ModelRanking($this->modelSession->ranking);

        $this->view->modes = $modelRanking->getModes();
        $this->view->mode = $modelRanking->getMode();
        $this->view->models = $modelRanking->getRankingModels();
        $this->view->modelCount = $modelRanking->getModelCount();
    }

    public function statsAction()
    {
        $modelTable = new God_Model_ModelTable();
        $this->view->rankings = $modelTable->getRankingStats();
    }
    
    public function thumbnailerAction()
    {
        $photosetTable = new God_Model_PhotosetTable;;
        $query = $photosetTable->getThumbnails();
        
        $paginator = new Doctrine_Pager($query, $this->_getParam('page',1), 18 );

        $photosets = $paginator->execute();

        $this->view->paginator = $paginator;
        $this->view->photosets = $photosets;
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
