<?php
class God_Model_WebResource extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('webResources');

        $this->hasColumn('id', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));

        $this->hasColumn('website', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('xpathfilter', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('date', 'timestamp', 25, array(
                'type'               => 'timestamp',
                'length'             => '25'
        ));

        $this->hasColumn('imagexpath', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('nextCheck', 'timestamp', 25, array(
                'type'               => 'timstamp',
                'length'             => '25'
        ));

        $this->hasColumn('sitescan', 'boolean', 25, array(
                'type'               => 'boolean',
                'length'             => '25'
        ));

        $this->hasColumn('frequency', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('sitescanxpath', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('sitescanurl', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));
    }
}