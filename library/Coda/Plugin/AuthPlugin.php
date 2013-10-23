<?php
class Coda_Plugin_AuthPlugin extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $user = null;
        $auth = Zend_Auth::getInstance();

        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $user     = Doctrine_Core::getTable('Coda_Model_User')->findOneBy('email', $identity);
        }
        $request->setParam('user', $user);
    }
}
