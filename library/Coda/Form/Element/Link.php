<?php
class Coda_Form_Element_Link extends Zend_Form_Element
{
    public $helper = 'formLink';
    public function init()
    {
    	$view = $this->getView();
    	$view->addHelperPath(APPLICATION_PATH.'/../library/Coda/View/Helper', 'Coda_View_Helper');
    }
}