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
            $link = $links->item($i);
            $linksArray[$i]['href'] = $link->getAttribute('href');
        }

        return $linksArray;
    }
}