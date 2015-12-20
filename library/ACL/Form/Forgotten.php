<?php
class ACL_Form_Forgotten extends Twitter_Bootstrap_Form_Horizontal
{
    public function init()
    {
        $this->setLabelColSize(4);
        $this->setFieldColSize(8);
        $this->setColType('sm');

        $this->addElement("text", "emailaddress", array(
                "label" => "Username",
                "required" => true,
                "placeholder" => "Your email address",
        ))

        ->addElement("submit", "login", array(
            "label" => "Send Email"
        ));
    }
}
