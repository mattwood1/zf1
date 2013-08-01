<?php

class Application_Model_DbTable_Models extends Zend_Db_Table_Abstract
{
	protected $_name = 'models';
	protected $_rowClass = 'Application_Model_Model';

	

	public function addModel($name, $path, $uri) {
		$data = array(
				'name' => $name,
				'path' => $path,
				'uri' => $uri,
		);
		$this->insert($data);
	}

	public function updateModel($id, $name, $path, $uri) {
		$data = array(
				'name' => $name,
				'path' => $path,
				'uri' => $uri,
		);
		$this->update($data, 'ID ='. (int)$id);
	}

	public function deleteModel($id) {
		$data = array(
				'active' => (int)0,
		);
		$this->update($data, 'ID ='. (int)$id);
	}
}