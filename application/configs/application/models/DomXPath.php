<?php
class God_Model_DomXPath {

    protected $_dom; // DOMDocument Object

    public function __construct($html) {
        $this->_dom = new DOMDocument();
        @$this->_dom->loadHTML($html);
    }

    public function evaluate($path) {
        $linkxpath = new DOMXPath($this->_dom);
        $links = $linkxpath->evaluate($path);

        $linksArray = array();
        for ($i = 0; $i < $links->length; $i++) {
            if (preg_match("~(/img)~i", $path)) {
                // get images and links
                $img = $links->item($i);
                $href = $links->item($i)->parentNode;
                $href2 = $links->item($i)->parentNode->parentNode;
                if ($href->getAttribute('href')) {
                    $linksArray[$i]['href'] = addslashes($href->getAttribute('href'));
                } else {
                    $linksArray[$i]['href'] = addslashes($href2->getAttribute('href'));
                }
                $linksArray[$i]['img'] = addslashes($img->getAttribute('src'));
            } else {
                // get links
                $link = $links->item($i);
                $linksArray[$i]['href'] = $link->getAttribute('href');
            }
        }

        return $linksArray;
    }
}