<?php
class God_Model_Base_ImageHashIndex extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('imagehashindex');

        $this->hasColumn('id', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));

        $this->hasColumn('index', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('hash', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('image_id', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));
    }
    
    public function setUp()
    {
//        $this->hasMany('God_Model_ImageHash as hash', array(
//                'local'   =>  'hash',
//                'foreign' =>  'hash',
//                //'cascade' => array('delete')
//        ));
    }
    
}