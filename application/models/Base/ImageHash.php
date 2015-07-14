<?php
class God_Model_Base_ImageHash extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('imagehash');

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

        $this->hasColumn('hash', 'string', 50, array(
                'type'               => 'string',
                'length'             => '50'
        ));
    }
    
}