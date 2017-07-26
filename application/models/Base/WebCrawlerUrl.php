<?php
class God_Model_Base_WebCrawlerUrl extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('webcrawlerUrls');

        $this->hasColumn('id', 'integer', 11, array(
            'type' => 'integer',
            'fixed' => 0,
            'unsigned' => true,
            'primary' => true,
            'autoincrement' => true,
            'length' => '11',
        ));

        $this->hasColumn('url', 'string', 1000, array(
            'type' => 'string',
            'length' => '1000'
        ));

        $this->hasColumn('contenttype', 'string', 1000, array(
            'type' => 'string',
            'length' => '1000'
        ));

        $this->hasColumn('contentlength', 'integer', 11, array(
            'type' => 'integer',
            'length' => '11'
        ));

        $this->hasColumn('width', 'integer', 11, array(
            'type' => 'integer',
            'length' => '1000'
        ));

        $this->hasColumn('height', 'integer', 11, array(
            'type' => 'integer',
            'length' => '1000'
        ));

        $this->hasColumn('pixels', 'integer', 11, array(
            'type' => 'integer',
            'length' => '1000'
        ));

        $this->hasColumn('statuscode', 'integer', 11, array(
            'type' => 'integer',
            'length' => '11'
        ));

        $this->hasColumn('domain_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => '11'
        ));

        $this->hasColumn('followed', 'integer', 11, array(
            'type' => 'integer',
            'length' => '1000'
        ));

        $this->hasColumn('frequency', 'string', 1000, array(
            'type'               => 'string',
            'length'             => '1000'
        ));

        $this->hasColumn('date', 'timestamp', 25, array(
            'type'               => 'timstamp',
            'length'             => '25'
        ));
    }

    public function setUp()
    {
        $this->hasOne('God_Model_WebCrawlerDomain as domain', array(
            'local' => 'domain_id',
            'foreign' => 'id',
            //'cascade' => array('delete')
        ));

        $this->hasOne('God_Model_WebCrawlerLink as link', array(
            'local' => 'id',
            'foreign' => 'url_id'
        ));

        $this->hasMany('God_Model_WebCrawlerLink as links', array(
            'local' => 'id',
            'foreign' => 'parent_url_id',
            //'cascade' => array('delete')
        ));

        $this->hasMany('God_Model_WebCrawlerUrlModelName as modelnamelinks', array(
            'local' => 'id',
            'foreign' => 'webcrawler_url_id'
        ));

        $this->hasMany('God_Model_WebCrawlerUrl as suburls', array(
            'local' => 'id',
            'foreign' => 'id',
            'refclass' => 'God_Model_WebCrawlerLink'
        ));

    }
}