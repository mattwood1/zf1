<?php
class God_Model_WebURL extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('webUrls');

        $this->hasColumn('id', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));


        $this->hasColumn('webResourceId', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('url', 'string', 10000, array(
                'type'               => 'string',
                'length'             => '10000'
        ));

        $this->hasColumn('httpStatusCode', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('action', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('thumbnails', 'string', 100000, array(
                'type'               => 'string',
                'length'             => '100000'
        ));

        $this->hasColumn('links', 'string', 100000, array(
                'type'               => 'string',
                'length'             => '100000'
        ));

        $this->hasColumn('images', 'string', 100000, array(
                'type'               => 'string',
                'length'             => '100000'
        ));

        $this->hasColumn('linked', 'integer', 1, array(
                'type'               => 'integer',
                'length'             => '1'
        ));

        $this->hasColumn('dateCreated', 'date', 25, array(
                'type'               => 'date',
                'length'             => '25'
        ));
    }

    public function setUp()
    {
        $this->hasOne('God_Model_WebResource as webResource', array(
                'local'   =>  'webResourceId',
                'foreign' =>  'id',
                //'cascade' => array('delete')
        ));

        $this->hasMany('God_Model_ModelNameWebURL as ModelNameWebURL', array(
                'local'   =>  'id',
                'foreign' =>  'webUrl_id',
                //'cascade' => array('delete')
        ));
    }

}