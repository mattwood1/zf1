<?php
class God_Model_Base_ModelNameWebURL extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('model_name_webUrls');

        $this->hasColumn('id', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));


        $this->hasColumn('model_name_id', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('webUrl_id', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));
    }

    public function setUp()
    {
        $this->hasOne('God_Model_ModelName as modelName', array(
                'local'   =>  'model_name_id',
                'foreign' =>  'ID',
                //'cascade' => array('delete')
        ));

        $this->hasMany('God_Model_WebURL as webUrl', array(
                'local'   =>  'webUrl_id',
                'foreign' =>  'id',
                //'cascade' => array('delete')
        ));
    }
}