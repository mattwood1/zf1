<?php
class God_Form_Login extends Zend_Form
{
	public function init()
	{
		// Make this form horizontal
		//$this->setAttrib("horizontal", true);

		$this->addElement("text", "username", array(
			"label" => "Username",
			"placeholder" => "Your username"
		))

		->addElement("password", "password", array(
			"label" => "Password",
			"required" => true,
			"placeholder" => "Your password"
		))

		->addElement("submit", "login", array("label" => "Login"))

		/*
		->addElement('Link', 'forgotton', array(
				'label' => 'Forgotton Password',
				'url' => array('action' => 'forgotton')
		))
		/*
		->addElement("link", 'register', array(
				'label' => 'Registration',
				'url' => array('action' => 'registration')
		))
		*/
		;
	}
}