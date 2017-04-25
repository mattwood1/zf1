<?php
class God_Model_Base_WebCrawler extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('webcrawler');

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

        $this->hasColumn('url', 'string', 1000, array(
            'type' => 'string',
            'length' => '1000'
        ));

        $this->hasColumn('statuscode', 'integer', 11, array(
            'type' => 'integer',
            'length' => '1000'
        ));

        $this->hasColumn('contenttype', 'string', 1000, array(
            'type' => 'string',
            'length' => '1000'
        ));

        $this->hasColumn('contentlength', 'integer', 11, array(
            'type' => 'integer',
            'length' => '1000'
        ));

        $this->hasColumn('parent', 'integer', 11, array(
            'type' => 'integer',
            'length' => '1000'
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
}