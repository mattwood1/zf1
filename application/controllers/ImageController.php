<?php

class ImageController extends Coda_Controller
{
    protected $_largeWidth = 800;
    protected $_mediumWidth = 400; // Desktop 400, BlackBerry 150
    protected $_thumbWidth = 150;
    protected $_height = 200;
    protected $_ratio = 1.333;
    protected $_quality = 100; // percent
    protected $_orientation = "portrait";

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function thumbnailAction()
    {
        $this->_height('thumb');
        $image = new God_Model_Image();
        return $image->process($this->_getParam('id'), $this->_thumbWidth, $this->_height, $this->_quality, $this->_thumbWidth.':'.$this->_height);
    }

    public function mediumAction()
    {
        $this->_height('medium');
        $image = new God_Model_Image();
        return $image->process($this->_getParam('id'), $this->_mediumWidth, $this->_height, $this->_quality, $this->_mediumWidth.':'.$this->_height);
    }

    public function largeAction()
    {
        $this->_height('large');
        $image = new God_Model_Image();
        return $image->process($this->_getParam('id'), $this->_largeWidth, $this->_height, $this->_quality, null);
    }

    public function fullAction()
    {
        // action body
        // TODO: needs a view image/full.phtml
        $this->view->image = $this->_getParam('id');
    }

    public function externalAction()
    {
        if ($this->_request->getParam('referer') && $this->_request->getParam('url')) {
            header("Content-Type: image/jpeg");
            $cache = Zend_Cache::factory('Core', 'Memcached');

            $image = $cache->load(md5($this->_request->getParam('referer').'_'.$this->_request->getParam('url').'_'.$this->_request->getParam('width')));
            if ($this->_request->getParam('ignorecache') == 1) {
                $image = false;
            }

            if (!$image) {
                $curl = new God_Model_Curl;
                $curl->Curl($this->_request->getParam('url'), $this->_request->getParam('referer'), true, 2);

                ob_start();
                echo $curl->image($this->_request->getParam('width'));
                $image = ob_get_clean();

                $cache->save($image, md5($this->_request->getParam('referer').'_'.$this->_request->getParam('url').'_'.$this->_request->getParam('width')));
            }
            echo $image;
            exit;
        }
    }

    protected function _orientation()
    {
        list($width,$height) = getimagesize($_SERVER['DOCUMENT_ROOT'].urldecode($this->_getParam('id')));
        if ($width > $height) {
            $this->_orientation = "landscape";
        }
    }

    protected function _height($width)
    {
        $this->_browserDetection();
        $this->_orientation();
        if ($this->_orientation == "portrait") {
            $this->_height = floor($this->{'_'.$width.'Width'}*$this->_ratio);
        } else {
            $this->_height = floor($this->{'_'.$width.'Width'}/$this->_ratio);
        }
    }

    protected function _browserDetection()
    {
        switch(true) {
            case stristr($_SERVER['HTTP_USER_AGENT'], 'Mobile'):
                $this->_largeWidth = 320;
                $this->_mediumWidth = 150;
                $this->_thumbWidth = 100;
                $this->_largeWidth = floor($this->_largeWidth*2);
                break;
        }
    }
}