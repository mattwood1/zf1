<?php
class Application_Model_Model extends Zend_Db_Table_Row {

	public function getModel($id)
	{
		$model = new Application_Model_DbTable_Models();
		$id = (int)$id;
		$row = $model->fetchRow('ID = ' . $id);
		if (!$row) {
			throw new Exception("Could not find row $id");
		}
		return $row;
	}

	public function getModelName()
	{
		$modelnames = new Application_Model_DbTable_ModelNames();
		return $modelnames->getModelNameByModel($this);
	}

	public function getModelAlias()
	{
		$modelnames = new Application_Model_DbTable_ModelNames();
		return $modelnames->getModelAliasByModel($this);
	}

	public function getModelPhotosets()
	{
		$photosets = new Application_Model_DbTable_Photosets();
		return $photosets->getModelPhotosets($this);
	}

	public function getModelLatestPhotoset()
	{
	    $photoset = new Application_Model_DbTable_Photosets();
	    return $photoset->getModelLatestPhotoset($this);
	}
}