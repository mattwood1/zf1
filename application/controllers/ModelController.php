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
        $model = new Application_Model_DbTable_Models();
        $this->view->models = $model->fetchAll();
    }

    public function viewAction()
    {
        // add body
        $model = Doctrine_Core::getTable('God_Model_Model')
            ->createQuery('m')
            ->innerJoin('m.names n')
            ->innerJoin('m.photosets p')

            ->where('m.ID = ?', $this->_request->getParam('id'))
            ->andWhere('m.active = ?', 1)
            ->andWhere('n.default = ?', 1)
            ->andWhere('p.active = ?', 1)
            ->orderBy('p.name asc');

        $model = $model->execute();

        $this->view->model = $model[0];
    }

    public function addAction()
    {
        // add body
        $form = new Application_Form_Model();
        $form->submit->setLabel('Add');
        $this->view->form = $form;
    }

    public function editAction()
    {
        // edit body
        $form = new Application_Form_Model();
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
                $models = new Application_Model_DbTable_Models();
                $models->updateModel($id, $name, $path, $uri);
                $this->_helper->redirector('view');
            } else {
                $form->populate($formData);
            }
        } else {
            $id = $this->_getParam('id', 0);
            if ($id > 0) {
                $models = new Application_Model_DbTable_Models();
                $form->populate($models->getModel($id));
            }
        }
    }

    public function deleteAction()
    {
        // delete body
    }

}

