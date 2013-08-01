<?php

class Application_Model_DbTable_Photosets extends Zend_Db_Table_Abstract
{
	protected $_name = 'photosets';
	protected $_rowClass = 'Application_Model_Photoset';

	public function getModelPhotosets(Application_Model_Model $model)
	{
		return $this->fetchAll('`model_id` = '.$model->ID.' AND `active` = 1', 'name ASC');
	}

	public function getModelLatestPhotoset(Application_Model_Model $model)
	{
	    return $this->fetchRow('`model_id` = '.$model->ID. ' AND `manual_thumbnail` = 1 AND `active` = 1', 'id DESC');
	}
}