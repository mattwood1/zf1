<?php
class Coda_View_Helper_FormLink extends Zend_View_Helper_FormElement
{
    public function formNote($name, $value = null, $options = null)
    {
        $info = $this->_getInfo($name, $value, $options);
        extract($info); // name, value, attribs, options, listsep, disable
        $xhtml = '<a id="' . $name . '" href="' . $this->url($options['url']) . '" class="btn">' . $value . '</a>';
        
        return $xhtml;
    }
}