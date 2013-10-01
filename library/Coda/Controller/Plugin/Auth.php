<?php
class Coda_Controller_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $auth = Zend_Auth::getInstance();
        if (! $auth->hasIdentity()) {
            // Defer more complex authentication logic to AuthController
            if ('auth' != $this->getRequest()->getControllerName()) {
                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $redirector->gotoSimple('login', 'auth');
            }
        }
    }
}