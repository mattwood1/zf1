<?php
	
	class Coda_Doctrine_Validate_RecordExists extends Zend_Validate_Abstract
	{
		
		protected $_table;
		protected $_field;

		const EXISTS = 'exists';

		protected $_messageTemplates = array(
			self::EXISTS => 'This value does not exist in the database.'
		);

		public function __construct( $tableName, $fieldName )
		{
			$table = null;

			if( is_null( $table = Doctrine_Core::getTable( $tableName ) ) ) {
				
				return null;
			}

			if( ! $table->hasColumn( $fieldName ) ) {
				
				return null;
			}

			$this->_table = $table;
			$this->_field = $fieldName;
		}

		public function isValid( $value )
		{
			$this->_setValue( $value );


			if( ! count( $this->_table->findBy( $this->_field, $value) ) ) {

				$this->_error(self::EXISTS);
				return false;
			}

			return true;
		}
	}
?>
