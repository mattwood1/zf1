<?php
class God_Model_Base_ImageDuplicates extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('image_duplicates');

        $this->hasColumn('id', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));

        $this->hasColumn('image_id', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('duplicate_image_id', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));
    }
    
}