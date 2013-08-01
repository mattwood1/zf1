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
        $models = new Application_Model_DbTable_Models();
        $model = $models->fetchRow('id = '.$this->_getParam('id'));
    	$this->view->model = $model;

    	$photosets = new Application_Model_DbTable_Photosets();
		$photoset = $photosets->fetchRow('`id` = '.$this->_getParam('photoset'));
		$this->view->photoset = $photoset;

		$path = $photoset->path;

		$data = array();
		if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].'/'.$path)) {
			//	$counter = 0;
			while (false !== ($files = readdir($handle))) {
				if ($files != "." && $files != "..") {		// remove '.' '..' directories
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