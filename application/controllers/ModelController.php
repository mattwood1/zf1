<?php

class ModelController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
        $model = new God_Model_Model();
        $this->view->models = $model->fetchAll();
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
        } else {
            $model = Doctrine_Core::getTable('God_Model_Model')
                ->createQuery('m')
                ->innerJoin('m.names n')
                ->where('m.ID = ?', $this->_request->getParam('id'));
            $model = $model->execute()->toArray();

            //echo '<pre>';var_dump($model);echo '</pre>';

            $form->populate($model[0]);
        }
    }

    public function deleteAction()
    {
        // delete body
    }

}

