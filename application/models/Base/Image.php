<?php
class God_Model_Base_Image extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('images');

        $this->hasColumn('id', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));

        $this->hasColumn('photoset_id', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11',
        ));

        $this->hasColumn('filename', 'string', 5000, array(
                'type'               => 'string',
                'length'             => '5000',
        ));

        $this->hasColumn('width', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11',
        ));

        $this->hasColumn('height', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11',
        ));

        $this->hasColumn('bits', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11',
        ));

        $this->hasColumn('channels', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11',
        ));

        $this->hasColumn('mime', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000',
        ));
    }
    
    public function setUp()
    {
//        $this->hasMany('God_Model_ModelName as names', array(
//                'local'   =>  'ID',
//                'foreign' =>  'model_id',
//                //'cascade' => array('delete')
//        ));
        
        $this->hasOne('God_Model_Photoset as photoset', array(
                    'local'   => 'photoset_id',
                    'foreign' => 'id'
        ));
        
        $this->hasOne('God_Model_ImageHash as hash', array(
                    'local'   => 'id',
                    'foreign' => 'image_id',
                    'cascade' => array('delete')
        ));
    }
}