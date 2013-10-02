<?php
class Coda_Plugin_InitPlugin extends Zend_Controller_Plugin_Abstract
{
    /**
     * @var Zend_View
     */
    protected $_view;
    
    /**
     * Set up the layout and get the view object used in the layout
     */
    public function __construct()
    {
    	$this->_view = Zend_Layout::startMvc(array(
                'layoutPath' => '../application/modules/default/views/layouts/'))
            ->setLayout('layout')->getView();
        
    }
    
    /**
     * Set up helper paths for the current request. 
     * View helpers are loaded from the default module and current request's module if different
     * 
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $this->_view->addHelperPath(APPLICATION_PATH . '/../library/Coda/View/Helper/', 'Coda_View_Helper_');
        $this->_view->addHelperPath(APPLICATION_PATH . '/../library/Coda/Form/Element/', 'Coda_Form_Element_');
        $this->_view->addHelperPath(APPLICATION_PATH . '/../models/View/Helper/', 'View_Helper_');
        
        Zend_Controller_Action_HelperBroker::addPath('Coda/Helper/', 'Coda_Helper_');
    }
}
