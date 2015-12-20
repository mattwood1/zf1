<?php

class ACL_Model_Acl extends Zend_Acl
{

    protected $_acl_roles = array(
        'guest'        => null,
        'user'         => 'guest',
        'admin'        => 'user'
    );

    protected $_acl_resources = array(

        // anyone who's not logged into the system
        'unauthenticated' => array(
            'modules' => array(
                    'default' => array(
                        'controllers' => array(
                            'auth'  => array('login', 'logout', 'forgotten', 'forbidden'),
                            'error' => array('*')
                        )
                            ),
            ),
            'allow' => array('guest')
        ),

        'user' => array(
            'modules' => array(
                'default' => array(
                    'controllers' => array(
                        'index'   => array('*'),
                        'user'    => array('*'),
                        'image'   => array('thumbnail', 'mini', 'medium', 'large', 'full'),
                        'model'   => array('view'),
                        'gallery' => array('view')
                    )
                )
            ),
            'allow' => array('user')
        ),

        'admin' => array(
            'modules' => array(
                'default' => array(
                    'controllers' => array(
                        'index'        => array('*'),
                        'image'        => array('*'),
                        'model'        => array('*'),
                        'gallery'      => array('*'),
                        'web-resource' => array('*'),
                        'web-url'      => array('*'),
                        'web-link'     => array('*'),
                        'test'         => array('*')
                    )
                )
            ),
            'allow' => array('admin'),
        )

    );

    public function __construct()
    {
        // add all defined gd_roles
        foreach ($this->_acl_roles as $role => $parents) {

            if ($parents) {

                $this->addRole(new Zend_Acl_Role($role), explode(',', $parents));
            }
            else {

                $this->addRole(new Zend_Acl_Role($role));
            }
        }

        // add all defined resources

        foreach ($this->_acl_resources as $resource => $attr) {
            $this->add(new Zend_Acl_Resource($resource));
				// set appropriate permissions
	            foreach (array('allow', 'deny') as $perm) {

	                if (isset($attr[$perm]) && is_array($attr[$perm]) && count($attr[$perm])) {

	                    foreach ($attr[$perm] as $role) {

	                        $this->$perm($role, $resource);
	                    }
	                }
	            }
        }

     }


    public function canDispatch($role, $module, $controller, $action)
    {

        foreach ($this->_acl_resources as $resource => $attr) {

            if(isset($attr['modules']) && is_array($attr['modules'])) {

                foreach($attr['modules'] as $res_module => $moduleAttr) {

                    if (isset($moduleAttr['controllers']) && is_array($moduleAttr['controllers'])) {

                        foreach ($moduleAttr['controllers'] as $res_ctrlr => $actions) {

                            if ($res_module == $module && $res_ctrlr == $controller) {

                                foreach ($actions as $res_action) {

                                    if ($res_action == $action || $res_action == '*') {

                                        if ($this->isAllowed($role, $resource)) {

                                            return true;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
}
