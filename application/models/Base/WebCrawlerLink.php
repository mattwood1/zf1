<?php
class God_Model_Base_WebCrawlerLink extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('webcrawlerLinks');

        $this->hasColumn('id', 'integer', 11, array(
            'type' => 'integer',
            'fixed' => 0,
            'unsigned' => true,
            'primary' => true,
            'autoincrement' => true,
            'length' => '11',
        ));

        $this->hasColumn('link', 'string', 1000, array(
            'type' => 'string',
            'length' => '1000'
        ));

        $this->hasColumn('url_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => '11'
        ));

        $this->hasColumn('parent_url_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => '11'
        ));

        $this->hasColumn('priority', 'integer', 11, array(
            'type' => 'integer',
            'length' => '11'
        ));
    }

    public function setUp()
    {
        $this->hasOne('God_Model_WebCrawlerUrl as url', array(
            'local' => 'url_id',
            'foreign' => 'id',
            //'cascade' => array('delete')
        ));

        $this->hasOne('God_Model_WebCrawlerUrl as parent_url', array(
            'local'   => 'parent_url_id',
            'foreign' => 'id'
        ));

        $this->hasOne('God_Model_WebCrawlerLink as parent_link', array(
            'local' => 'parent_url_id',
            'foreign' => 'url_id'
        ));

        $this->hasMany('God_Model_WebCrawlerLink as sublinks', array(
            'local' => 'url_id',
            'foreign' => 'parent_url_id'
        ));
    }
}