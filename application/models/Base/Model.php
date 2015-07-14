<?php
class God_Model_Base_Model extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('models');

        $this->hasColumn('ID', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));

        $this->hasColumn('name', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000',
        ));

        $this->hasColumn('path', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('uri', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('active', 'boolean', 25, array(
                'type'               => 'boolean',
                'length'             => '25'
        ));

        $this->hasColumn('ranking', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('date', 'date', 25, array(
                'type'               => 'date',
                'length'             => '25'
        ));

        $this->hasColumn('search', 'boolean', 25, array(
                'type'               => 'boolean',
                'length'             => '25'
        ));

        $this->hasColumn('searched', 'boolean', 25, array(
                'type'               => 'boolean',
                'length'             => '25'
        ));

        $this->hasColumn('datesearched', 'timestamp', 25, array(
                'type'               => 'timestamp',
                'length'             => '25'
        ));

        $this->hasColumn('rankDate', 'timestamp', 25, array(
                'type'               => 'timestamp',
                'length'             => '25'
        ));

        $this->hasColumn('photosetsChecked', 'date', 25, array(
                'type'               => 'date',
                'length'             => '25'
        ));


    }

    public function setUp()
    {
        $this->hasMany('God_Model_ModelName as names', array(
                'local'   =>  'ID',
                'foreign' =>  'model_id',
                //'cascade' => array('delete')
        ));

        $this->hasMany('God_Model_Photoset as photosets', array(
                'local'   =>  'ID',
                'foreign' =>  'model_id',
                //'cascade' => array('delete')
        ));

        $this->hasMany('God_Model_WebLink as webLinks', array(
                'local'   =>  'ID',
                'foreign' =>  'model_id',
                //'cascade' => array('delete')
        ));
    }
}