<?php
class God_Form_Login extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {
        $this->setColType('sm');
        $this->setLabelColSize('4');
        $this->setFieldColSize('8');
        
        $this->addElement("text", "username", array(
                "label" => "Username",
                "required" => true,
                "placeholder" => "Your username",
                'decorators' => array('ViewHelper')
        ))

        ->addElement("password", "password", array(
                "label" => "Password",
                "required" => true,
                "placeholder" => "Your password"
        ))

        ->addElement("submit", "login", array(
            "label" => "Log in"
        ));
    }
}