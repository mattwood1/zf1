<?php
class God_Model_Base_WebCrawlerDomain extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('webcrawlerDomains');

        $this->hasColumn('id', 'integer', 11, array(
            'type' => 'integer',
            'fixed' => 0,
            'unsigned' => true,
            'primary' => true,
            'autoincrement' => true,
            'length' => '11',
        ));

        $this->hasColumn('domain', 'string', 1000, array(
            'type' => 'string',
            'length' => '1000'
        ));

        $this->hasColumn('allowed', 'integer', 11, array(
            'type' => 'integer',
            'length' => '11'
        ));
    }

    public function setUp()
    {
        $this->hasMany('God_Model_WebCrawlerUrl as urls', array(
            'local'   =>  'id',
            'foreign' =>  'domain_id',
            //'cascade' => array('delete')
        ));
    }
}