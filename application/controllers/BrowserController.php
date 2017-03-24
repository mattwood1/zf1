<?php

class BrowserController extends Coda_Controller
{
    public function init()
    {
        $this->_helper->_layout->setLayout('safe');
    }

    public function indexAction()
    {
        $curl = new God_Model_Curl;
        $originalUrl = $curl->normalizeURL($this->_request->getParam('url'));

        $html = $curl->Curl($originalUrl, false, false, 5, true);
        $url = $curl->lastUrl(); // Following redirects

        if ($curl->contentType() == "text/html") {

        }

        $imageElements = array();
        switch ($curl->contentType()) {
            case 'text/html':
                $dom = new God_Model_DomXPath($html);
                $imageElements = $dom->evaluate("//a//img");
                break;
            case 'image/jpeg':
                break;
        }

        $this->view->url = $url;
        $this->view->originalUrl = $originalUrl;
        $this->view->contentType = $curl->contentType();
        $this->view->html = $html;
        $this->view->imageElements = $imageElements;
    }
}