<?php

class Application_Model_DbTable_ModelNames extends Zend_Db_Table_Abstract
{
	protected $_name = 'model_names';
	protected $_rowClass = 'Application_Model_ModelNames';
	
	public function getModelNameByModel(Application_Model_Model $model){
		return $this->fetchRow('`model_id` = '.$model->ID.' AND `default` = "1"');
	}
	
	public function getModelAliasByModel(Application_Model_Model $model){
		return $this->fetchAll('`model_id` = '.$model->ID.' AND `default` = "0"');
	}

	public function getAllModelNamesByModel(Application_Model_Model $model){
		return $this->fetchAll('`model_id` = '.$model->ID);
	}
}