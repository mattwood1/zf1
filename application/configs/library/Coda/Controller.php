<?php
class Coda_Controller extends Zend_Controller_Action
{
    /**
     * Add a new flash message to the session
     * 
     * @param type $message
     * @param type $type
     */
    protected function _flash($message, $type = Coda_Helper_Flash::SUCCESS)
    {
        $this->_helper->flash($message, $type);
    }
    
    /**
     * Proxy to zend's redirector action helper
     * 
     * @param array $urlParams
     * @param string $route
     */
    protected function gotoRoute(array $urlParams = array(), $route = null, $reset = false)
    {
        $this->_helper->redirector->gotoRoute($urlParams, $route, $reset);
    }
    
    /**
     * Redirect back to the current page.
     */
    protected function _redirectBack()
    {
        $this->_helper->redirector->gotoUrl($this->_request->getServer('HTTP_REFERER'));
    }
    
    /**
     * Disable the layout
     */
    protected function _disableLayout()
    {
        $this->_helper->layout()->disableLayout(true);
    }
}