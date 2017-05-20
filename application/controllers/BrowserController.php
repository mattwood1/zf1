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
        $links = array();
        $images = array();
        $html = '';

        if ($this->_request->getParam('url')) {

            $html = $curl->Curl($this->_request->getParam('url'));

            // DOM
            $dom = new DOMDocument();
            @$dom->loadHTML($html);

            $xpath = new DOMXPath($dom);

            $aTag = $xpath->evaluate('//a');
            for ($i = 0; $i < $aTag->length; $i++) {
                $link = $aTag->item($i);
                $href = $link->getAttribute('href');
                $links[] = trim($curl->normalizeURL($href, $this->_request->getParam('url')));
            }

            if ($this->_request->getParam('filter')) {
                foreach ($links as $key => $link) {
//                    $links[$key] = preg_replace("~\&s=\w+~", "", $link);
                }
            }

            $imgTag = $xpath->evaluate('//img');
            for ($i = 0; $i < $imgTag->length; $i++) {
                $image = $imgTag->item($i);
                $src = $image->getAttribute('src');
                $images[] = trim($curl->normalizeURL($src, $this->_request->getParam('url')));
            }

            if (!$links) {
                $curlinfo = $curl->curlInfo();
                $links[] = $curlinfo['redirect_url'];
            }

        }

//        $originalUrl = $curl->normalizeURL($this->_request->getParam('url'));

//        $html = $curl->Curl($originalUrl, false, false, 5, true);
//        $url = $curl->lastUrl(); // Following redirects
//
//        if ($curl->contentType() == "text/html") {
//
//        }
//
//        $imageElements = array();
//        switch ($curl->contentType()) {
//            case 'text/html':
//                $dom = new God_Model_DomXPath($html);
//                $imageElements = $dom->evaluate("//a//img");
//                break;
//            case 'image/jpeg':
//                break;
//        }
//
        $this->view->url = $this->_request->getParam('url');
//        $this->view->originalUrl = $originalUrl;
//        $this->view->contentType = $curl->contentType();
        $this->view->html = $html;
        $this->view->links = $links;
        $this->view->images = $images;
        $this->view->curl = $curl;
//        $this->view->imageElements = $imageElements;
    }
}