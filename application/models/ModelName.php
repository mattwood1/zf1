<?php
class God_Model_ModelName extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('model_names');

        $this->hasColumn('ID', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));

        $this->hasColumn('model_id', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11',
        ));

        $this->hasColumn('name', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000',
        ));

        $this->hasColumn('default', 'boolean', 25, array(
                'type'               => 'string',
                'length'             => '1000',
        ));

        $this->hasColumn('webLinkTargetId', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11',
        ));

        $this->hasColumn('datesearched', 'timestamp', 25, array(
                'type'               => 'timestamp',
                'length'             => '25',
        ));
    }

    public function setUp()
    {
        $this->hasMany('God_Model_ModelNameWebURL as webUrls', array(
                'local'   =>  'ID',
                'foreign' =>  'model_name_id',
                //'cascade' => array('delete')
        ));
    }
}