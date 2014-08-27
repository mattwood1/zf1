<?php
class God_Model_WebLink extends Doctrine_Record
{
    const webLink_Google    = 0;
    const weblink_GetThumbs = 1;
    const webLink_GotThumbs = 2;
    const webLink_Problem   = 3;
    const webLink_GetImages = 5;
    const webLink_GotImages = 6;

    public function setTableDefinition()
    {
        $this->setTableName('webLinks');

        $this->hasColumn('id', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));

        $this->hasColumn('webresourceid', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('model_id', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('url', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('statusCode', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('action', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('thumbnails', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('links', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('images', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('dateCreated', 'date', 25, array(
                'type'               => 'date',
                'length'             => '25'
        ));
    }

    public function setUp()
    {
        $this->hasOne('God_Model_Model as model', array(
                'local'   =>  'model_id',
                'foreign' =>  'ID',
                //'cascade' => array('delete')
        ));
    }
}