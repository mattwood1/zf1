<?php 
class LS_Form_User_Login extends Twitter_Bootstrap_Form_Vertical 
{
    public function init()
    {
        $this->addElement('text', 'emailaddress', array(
                            'placeholder' => 'Email Address',
                            'required' => true,
                            'decorators' => array('ViewHelper')
        ));

        $this->addElement('password', 'password', array(
                            'placeholder' => 'Password',
                            'required' => true,
                            'decorators' => array('ViewHelper')
        ));
      
        $this->addElement('submit', 'Login', array(  
                        'buttonType' => 'primary',
                        'type'       => 'submit',
                        'class' => 'btn-block btn-lg' 
                    ));

        $this->setAttrib('class', 'form-signin');
    }


}
