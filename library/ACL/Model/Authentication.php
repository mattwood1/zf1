<?php

/**
 * ACL_Model_Authentication
 *
 * Singleton class to handle user authentication / authorisation in the system.
 *
 * @author Dan Haworth <dan@xigen.co.uk>
 */
class ACL_Model_Authentication
{
    const LOGIN_SUCCESS_PASSCHANGE  =  1;
    const LOGIN_SUCCESS             =  0;
    const LOGIN_MISSING_CREDENTIALS = -1;
    const LOGIN_INVALID_CREDENTIALS = -2;
    const LOGIN_ACCOUNT_LOCKED      = -3;

    protected static $_instance;

    protected $_auth;
    protected $_adapter;
    protected $_acl;
    protected $_current_user;
    protected $_current_role;

    /**
     * __construct Class constructor
     *
     * @access protected
     * @return void
     */
    protected function __construct()
    {
        // Get working instances of Zend_Auth and an Auth_Adapter
        $connection     = Doctrine_Core::getConnectionByTableName('God_Model_User');
        $this->_acl     = new ACL_Model_Acl;
        $this->_auth    = Zend_Auth::getInstance();
        $this->_adapter = new ACL_Model_Table($connection);

        $this->_adapter
            ->setTableName('God_Model_User')
            ->setIdentityColumn('username')
            ->setCredentialColumn('password');

        if ($this->_auth->hasIdentity()) {

            // get the current user record and refresh it
            $this->_current_user = $this->_auth->getIdentity();
            if (!is_object($this->_current_user)) {
                _dexit($this->_current_user);
                $this->logOut ();
            }
            $this->_current_user->refresh();

            // set the current user role
            $this->setCurrentRole($this->_current_user->role);

        } else {
            $this->setCurrentRole('guest');
        }
    }

    /**
     * getInstance Returns singleton instance of the model
     *
     * @access public
     * @return ACL_Model_Authentication instance
     */
    public function getInstance()
    {
        return self::$_instance ?: (self::$_instance = new self);
    }

    /**
     * isLoggedIn Check if user is logged in
     *
     * @access public
     * @return boolean login status
     */
    public function isLoggedIn()
    {
        return $this->_auth->hasIdentity();
    }

    /**
     * logIn - Attempts to authenticate and login the user
     *
     * @param string $credential
     * @param string $password
     * @access public
     * @return intger LOGIN_* const status code.
     */
    public function logIn($credential, $password)
    {

        if (!($credential && $password)) {

            return self::LOGIN_MISSING_CREDENTIALS;
        }
        
        if (!$user = God_Model_UserTable::getInstance()->findOneByUsername($credential)) {

            return self::LOGIN_INVALID_CREDENTIALS;
        }

        /**
         *  Technically the Zend_Auth_Adapter wont support verifying BCrypt hashes internally
         *  so we verify it here and then pass through the verified hash as the credential if
         *  its confirmed to match.
         */
        if (md5($password) !== $user->password) {
            return self::LOGIN_INVALID_CREDENTIALS;
        }

        $this->_adapter->setIdentity($user->username);
        $this->_adapter->setCredential($user->password);

        $result = $this->_auth->authenticate($this->_adapter);

        if ($result->isValid()) {

            $this->_current_user = $user;
            $this->_auth->getStorage()->write($this->_current_user);
            $this->setCurrentRole($this->_current_user->role);
            
            return self::LOGIN_SUCCESS;
        }
        else {

            /**
             *  Should never get hit, unless the user changed their password
             *  somewhere between password_verify and authenticate. Unlikely.
             */

            return self::LOGIN_INVALID_CREDENTIALS;
        }
    }

    /**
     * logOut Clears the logged in identity and resets guest role
     *
     * @access public
     * @return void
     */
    public function logOut()
    {
        $this->_auth->clearIdentity();
        $this->setCurrentRole('guest');
    }

    /**
     * setCurrentRole Sets the current logged in users role
     *
     * @param mixed $role
     * @access public
     * @return boolean Was role switched successfully
     */
    public function setCurrentRole($role)
    {
        if ($this->_acl->hasRole($role)) {

            $this->_current_role = $role;
            return true;
        }

        return false;
    }

    /**
     * getCurrentRole Returns the current logged in users role
     *
     * @access public
     * @return string
     */
    public function getCurrentRole()
    {
        return $this->_current_role;
    }

    /**
     * getCurrentUser Returns the current logged in user
     *
     * @access public
     * @return ACL_Model_User
     */
    public function getCurrentUser()
    {
        return $this->_current_user ?: null;
    }

    /**
     * authoriseResource Returns whether a resource can be accessed under the current role.
     *
     * @param string $resource
     * @access public
     * @return boolean
     */
    public function authoriseResource($resource)
    {
        return $this->_acl->isAllowed($this->getCurrentRole(), $resource);
    }

    /**
     * authoriseControllerAction Returns whether the current role can dispatch a controller action.
     *
     * @param string $controller
     * @param string $action
     * @access public
     * @return boolean
     */
    public function authoriseControllerAction($module, $controller, $action)
    {
        return $this->_acl->canDispatch($this->getCurrentRole(), $module,  $controller, $action);
    }

    /**
     * authoriseRequest Returns whether a request can be dispatched under the current role.
     *
     * @param Zend_Controller_Request_Abstract $request
     * @access public
     * @return boolean
     */
    public function authoriseRequest(Zend_Controller_Request_Abstract $request)
    {

        return $this->authoriseControllerAction($request->getModuleName(), $request->getControllerName(), $request->getActionName());
    }
}
