<?php
class God_Model_Base_WebCrawlerUrlPhotosets extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('webcrawlerUrlPhotosets');

        $this->hasColumn('id', 'integer', 11, array(
            'type' => 'integer',
            'fixed' => 0,
            'unsigned' => true,
            'primary' => true,
            'autoincrement' => true,
            'length' => '11',
        ));

        $this->hasColumn('photoset_id', 'integer', 11, array(
            'type'               => 'integer',
            'length'             => '11'
        ));

        $this->hasColumn('url_id', 'integer', 11, array(
            'type'               => 'integer',
            'length'             => '11'
        ));
    }
}