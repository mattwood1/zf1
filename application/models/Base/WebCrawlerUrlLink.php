<?php
class God_Model_Base_WebCrawlerUrlLink extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('webcrawlerUrlLink_ref');

        $this->hasColumn('id', 'integer', 11, array(
            'type' => 'integer',
            'fixed' => 0,
            'unsigned' => true,
            'primary' => true,
            'autoincrement' => true,
            'length' => '11',
        ));

        $this->hasColumn('link_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => '11'
        ));

        $this->hasColumn('url_id', 'integer', 11, array(
            'type' => 'integer',
            'length' => '11'
        ));
    }

    public function setUp()
    {
        $this->hasOne('God_Model_WebCrawlerLink as link', array(
            'local' => 'link_id',
            'foreign' => 'id',
        ));

        $this->hasMany('God_Model_WebCrawlerUrl as url', array(
            'local' => 'url_id',
            'foreign' => 'id'
        ));
    }
}