<?php
class Coda_View_Helper_FormLink extends Zend_View_Helper_FormElement
{
    public function formLink($name, $value = null, $options = null)
    {
        $info = $this->_getInfo($name, $value, $options);
        extract($info); // name, value, attribs, options, listsep, disable

        $infotrim = explode(" ", $info['attribs']['class']);
        $key = array_search("form-control", $infotrim);
        if ($key) {
            unset($infotrim[$key]);
            $info['attribs']['class'] = implode(" ", $infotrim);
        }

        $xhtml = '<a id="' . $name . '" href="' . $info['attribs']['url'] . '" class="'. $info['attribs']['class'].'">' . $value . '</a>';

        return $xhtml;
    }
}