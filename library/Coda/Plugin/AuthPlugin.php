<?php
class Coda_Plugin_AuthPlugin extends Zend_Controller_Plugin_Abstract
{
	/*
    protected $_allowedPublic = array(
        'default' => null,
        'auth'    => null,
        'product' => array('display'),
        'user'    => array('registration'),
        'customer' => array('registration'),
        'cart'    => null,
        'content' => array('display'),
        'order'   => array('notification')
    );
    */

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        //$request->setParam('acl', Acl::getInstance());

        $user = null;
        $auth = Zend_Auth::getInstance();
        
        if ($auth->hasIdentity()) {
            $identity = $auth->getIdentity();
            $user     = Doctrine_Core::getTable('Coda_Model_User')->findOneBy('email', $identity);
        }

        //inject the user into the session
        $request->setParam('user', $user);
/*
        //check the user is allowed to login (they haven't been suspended or something like that)
        if ($user && ($message = $user->isAllowedToLogin()) !== true) {
            $this->_noAuth($message);
        }
*/
/*
        //get the acl for the user and inject it into the session
        $request->acl->setUser($user);
      
        if ($user) {
            $request->setParam('userId', $user->userId());
        }
*/      
    }
/*
    protected function _noAuth($message)
    {
        Xigen_Helper_SessionRedirect::getNamespace()->url = $this->getRequest()->getRequestUri();
        Zend_Controller_Action_HelperBroker::getStaticHelper('flash')
                ->direct($message, Xigen_Helper_Flash::ERROR);

        header('Location: /');
        exit;
    }
*/
}
