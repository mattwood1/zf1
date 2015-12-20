<?php
class AuthController extends Coda_Controller
{
    protected $_auth;
    
    public function init()
    {
        /* Initialize action controller here */
        
        $this->_auth = ACL_Model_Authentication::getInstance();
    }

    public function indexAction()
    {
        $this->gotoRoute(array('action' => 'login'));
    }
    
    public function loginAction()
    {
        $form = new ACL_Form_Login();

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {

            // process authentication
            switch($this->_auth->logIn($form->getValue('username'), $form->getValue('password'))) {
                case ACL_Model_Authentication::LOGIN_MISSING_CREDENTIALS :
                    $this->_helper->flash->addError('Some login details are missing');
                    break;

                case ACL_Model_Authentication::LOGIN_INVALID_CREDENTIALS :
                    $this->_helper->flash->addError('Invalid login details');
                    break;

                case ACL_Model_Authentication::LOGIN_ACCOUNT_LOCKED :
                    $this->_helper->flash->addWarning('This account has been locked');
                    break;

                case ACL_Model_Authentication::LOGIN_SUCCESS :
                    $this->_helper->redirector->gotoRoute(array('controller' => 'index', 'action' => 'index'),null, true);
                    break;
            }
        }

        $this->view->headTitle('Log in');
        $this->view->form = $form;
    }
    
    public function forgottenAction()
    {
        if ($this->_request->getParam('email') && $this->_request->getParam('key')) {
            $user = App_Model_UserTable::getInstance()->findOneByEmailaddress($this->_request->getParam('email'));
    
            if ($user && $this->_request->getParam('key') == $this->_generateKey($user)) {
                $form = new ACL_Form_PasswordReset();
                
                if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                    $user->password = md5($form->getValue('password1'));
                    $user->save();
                }
            }
            
        } else {
            $form = new ACL_Form_Forgotten();

            if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
                $user = App_Model_UserTable::getInstance()->findOneByEmailaddress($form->getValue('emailaddress'));
                $user->updated_at = date("Y-m-d h:i:s");
                $user->save();

                $mail = new Zend_Mail();
                $mail->setBodyText('This is the text of the mail.');
                $mail->setBodyHtml('<a href="http://competence.privatedns.org/auth/forgotten/email/' . $user->emailaddress . '/key/' . $this->_generateKey($user) . '">Reset your password</a>');
                $mail->setFrom('somebody@competence.privatedns.org', 'Competence');
                $mail->addTo($user->emailaddress, 'User');
                $mail->setSubject('Password Reset');
                $mail->send();
            }
        }
        
        $this->view->form = $form;
    }
    
    public function forbiddenAction()
    {
        
    }

    public function loginActionOLD()
    {
        $form = new God_Form_Login();

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            // process authentication
            $result = $this->_performLogin($form->getValues());
            if ($result) {
                if ($_SESSION['requestUrl']) {
                    $this->_helper->redirector->gotoUrl($_SESSION['requestUrl']);
                } else {
                    $this->gotoRoute(array('controller' => 'index', 'action' => 'index'));
                }
            }
        }

        $this->view->form = $form;
    }

    public function logoutAction()
    {
        $auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
        //$this->_flash('You have been loggged out', Coda_Helper_Flash::INFO);
        $this->gotoRoute(array('action' => 'login'));
    }

    protected function _performLogin( $credentials )
    {
        // get out auth adapter
        $authAdapter = $this->_getAuthAdapter();

        //var_dump($credentials, $authAdapter);exit;

        // set credentials
        $authAdapter->setIdentity  ($credentials['username']);
        $authAdapter->setCredential($credentials['password']);

        // attempt authentication
        $auth = Zend_Auth::getInstance();
        $result = $auth->authenticate( $authAdapter );

        // successful login
        if($result->isValid()) {
            $user = $authAdapter->getResultRowObject();
            //$this->_flash('Log in sucess', Coda_Helper_Flash::SUCCESS);
            return $user;
        }
        //$this->_flash('Log in failed', Coda_Helper_Flash::ERROR);
        return false;
    }

    public function changePasswordAction()
    {
        if (! $this->_request->user) {
            $this->gotoRoute(array('action' => 'login'));
        }

        Zend_Layout::getMvcInstance()->assign('clubs', $this->_request->clubs);
        $this->_helper->layout->setLayout('manage');

        $form = new User_Form_ChangePassword();

        if ($this->_request->isPost() && $form->isValid($this->_request->getPost())) {
            // check current password
            if (md5($form->getValue('password')) != $this->_request->user->password) {
                $this->_flash('Password is incorrect', Coda_Helper_Flash::ERROR);
                $this->_redirectBack();
            }

            $this->_request->user->password = md5($form->getValue('passwordNew'));
            $this->_request->user->save();



            // confirmation message
            $this->_flash('Your password has been changed', Coda_Helper_Flash::SUCCESS);
            $this->gotoRoute(array('module' => 'club', 'controller' => 'manage', 'action' => 'index'));
        }

        $this->view->form = $form;
    }


    // returns the autentication adaptor for a doctrine table
    protected function _getAuthAdapter() {

        $authAdapter = new Coda_Doctrine_Auth_Adapter(Doctrine_Core::getConnectionByTableName('God_Model_User'));

        $authAdapter
            ->setTableName('God_Model_User')
            ->setIdentityColumn('username')
            ->setCredentialColumn('password')
            ->setCredentialTreatment('MD5(?)');

        return $authAdapter;
    }

}