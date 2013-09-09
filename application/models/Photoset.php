<?php
class God_Model_Photoset extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('photosets');

        $this->hasColumn('id', 'integer', 11, array(
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

        $this->hasColumn('path', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('uri', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('thumbnail', 'string', 1000, array(
                'type'               => 'string',
                'length'             => '1000'
        ));

        $this->hasColumn('manual_thumbnail', 'boolean', 25, array(
                'type'               => 'boolean',
                'length'             => '25'
        ));

        $this->hasColumn('active', 'boolean', 25, array(
                'type'               => 'boolean',
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

    public function isActive()
    {
        if ($this->active) {
            return true;
        }
        return false;
    }

    public function isManualThumb()
    {
        if ($this->manual_thumbnail) {
            return true;
        }
        return false;
    }

}