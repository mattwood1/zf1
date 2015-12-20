<?php
class ACL_Form_PasswordReset extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {
        $this->setLabelColSize(4);
        $this->setFieldColSize(8);
        $this->setColType('sm');

        $this->addElement("password", "password1", array(
                "label" => "New Password",
                'validators'=> array(
                    'Alnum',
                    array('StringLength', array(6,20))
                ),
                'filters' => array('StringTrim'),
                "required" => true,
                "placeholder" => "Your new password",
        ))
        
        ->addElement("password", "password2", array(
                "label" => "Confirm Password",
                'validators'=> array(
                    array('Alnum', array('StringLength', array(6,20))),
                    array('identical', false, array('token' => 'password1'))
                ),
                'filters' => array('StringTrim'),
                "required" => true,
                "placeholder" => "Confirm new password",
        ))

        ->addElement("submit", "login", array(
            "label" => "Reset Password"
        ));
    }
}
