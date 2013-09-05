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

        $path = $model[0]->photosets[0]->path;

        $data = array();
        if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].'/'.$path)) {
            //    $counter = 0;
            while (false !== ($files = readdir($handle))) {
                if ($files != "." && $files != "..") {        // remove '.' '..' directories
                    if (is_file($_SERVER['DOCUMENT_ROOT'].$path.'/'.$files) == true) {
                        $counter = $this->three_digits( str_ireplace(".jpg", "", $files));
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
            echo 'Cannot open '.$_SERVER['DOCUMENT_ROOT'].'/'.$path;
        }

        ksort($data);
        $this->view->data = $data;
    }

    public function thumbnailAction()
    {
        echo 'This is to be written. Choose the gallery thumbnail.';
        exit;
    }

    function three_digits($value) {
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