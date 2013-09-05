<?php

class GalleryController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function viewAction()
    {
        // TODO: This should be getting a photoset - Removing the need for id being passed.

        $model = Doctrine_Core::getTable('God_Model_Model')
                ->createQuery('m')
                ->innerJoin('m.names n')
                ->innerJoin('m.photosets p')

                ->where('m.ID = ?', $this->_request->getParam('id'))
                ->andWhere('m.active = ?', 1)
                ->andWhere('n.default = ?', 1)
                ->andWhere('p.id = ?', $this->_getParam('photoset'))
                ->orderBy('p.name asc');

        $model = $model->execute();
        $this->view->model = $model[0];

        $this->view->files = $this->_getFiles($model[0]->photosets[0]->path);
    }

    public function thumbnailAction()
    {
        $photoset = Doctrine_Core::getTable('God_Model_Photoset')->findOneBy('id', $this->_request->getParam('photoset'));

        $this->view->photoset = $photoset;
        $this->view->files = $this->_getFiles($photoset->path);
    }

    protected function _getFiles($path)
    {
        $data = array();

        if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].'/'.$path)) {
            //    $counter = 0;
            while (false !== ($files = readdir($handle))) {
                if ($files != "." && $files != "..") {        // remove '.' '..' directories
                    if (is_file($_SERVER['DOCUMENT_ROOT'].$path.'/'.$files) == true) {
                        $counter = $this->_threeDigits( str_ireplace(".jpg", "", $files));
                        list($width,$height)=getimagesize($_SERVER['DOCUMENT_ROOT'].$path.'/'.$files);
                        $data[$counter]['uri'] = $path.'/'.$files;
                        $data[$counter]['name'] = $files;
                        $data[$counter]['width'] = $width;
                        $data[$counter]['height'] = $height;
                        $counter++;
                    } else {
                        echo '<p>'.$path.'/'.$files.' is a directory!</p>';
                    }
                }
            }
            closedir($handle);
        } else {
            return 'Cannot open '.$_SERVER['DOCUMENT_ROOT'].'/'.$path;
        }

        ksort($data);

        return $data;
    }

    protected function _threeDigits($value) {
        switch (strlen($value)) {
            case 1:
                $value = "00".$value;
                break;
            case 2:
                $value = "0".$value;
                break;
        }
        return $value;
    }
}