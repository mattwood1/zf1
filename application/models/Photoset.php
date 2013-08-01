<?php
class Application_Model_Photoset extends Zend_Db_Table_Row {
	public function getPhotoset($photoset_id) {
		$photosets = new Application_Model_DbTable_Photosets();
		return $photosets->fetchRow('`id` = '.$photoset_id);
	}
}