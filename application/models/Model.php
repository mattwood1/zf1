<?php
class God_Model_Model extends Doctrine_Record
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
/*
        $this->hasColumn('name', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000',
        ));
*/
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
    }
/*
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
*/
}