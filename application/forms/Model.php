<?php
class Application_Form_Model extends Zend_Form
{
	public function init()
	{
		$this->setName('model');
		$id = new Zend_Form_Element_Hidden('id');
		$id->addFilter('Int');
		
		$name = new Zend_Form_Element_Text('name');
		$name->setLabel('Name')
		->setRequired(true)
		->addFilter('StripTags')
		->addFilter('StringTrim')
		->addValidator('NotEmpty');
		
		$active = new Zend_Form_Element_Select('active');
		$active->setLabel('Active')
		->setOptions(array( 'Acitve' => 1, 'Inactive' => 0));
		
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setAttrib('id', 'submitbutton');
		$this->addElements(array($id, $name, $submit));
	}
}