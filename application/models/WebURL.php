<?php
class God_Model_WebURL extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('webUrls');

        $this->hasColumn('id', 'integer', 11, array(
                'type'               => 'integer',
                'fixed'              => 0,
                'unsigned'           => true,
                'primary'            => true,
                'autoincrement'      => true,
                'length'             => '11',
        ));


        $this->hasColumn('webResourceId', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('url', 'string', 10000, array(
                'type'               => 'string',
                'length'             => '10000'
        ));

        $this->hasColumn('httpStatusCode', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('action', 'integer', 11, array(
                'type'               => 'integer',
                'length'             => '11'
        ));

        $this->hasColumn('thumbnails', 'string', 100000, array(
                'type'               => 'string',
                'length'             => '100000'
        ));

        $this->hasColumn('links', 'string', 100000, array(
                'type'               => 'string',
                'length'             => '100000'
        ));

        $this->hasColumn('images', 'string', 100000, array(
                'type'               => 'string',
                'length'             => '100000'
        ));

        $this->hasColumn('linked', 'integer', 1, array(
                'type'               => 'integer',
                'length'             => '1'
        ));

        $this->hasColumn('dateCreated', 'date', 25, array(
                'type'               => 'date',
                'length'             => '25'
        ));
    }

    public function setUp()
    {
        $this->hasOne('God_Model_WebResource as webResource', array(
                'local'   =>  'webResourceId',
                'foreign' =>  'id',
                //'cascade' => array('delete')
        ));

        $this->hasMany('God_Model_ModelNameWebURL as ModelNameWebURL', array(
                'local'   =>  'id',
                'foreign' =>  'webUrl_id',
                //'cascade' => array('delete')
        ));
    }

    public function linkModelNameToUrl()
    {
        // Link Model Name
        $modelNameTable = new God_Model_ModelNameTable;
        $modelNames = $modelNameTable->getActiveModelNames();

        foreach ($modelNames as $modelName) {
            $name = str_replace(" ", "[\s\-\_]", $modelName->name);

            if (preg_match("~((?:[\-\/])" . $name . "(?:[\-\/$]))~i", $this->url)) { // ~(" . $name . ")~i is pants
                $this->linked = -5;                // Name Match found
                $this->action = God_Model_WebURLTable::GET_THUMBNAILS; // Set to get thumbs

                $modelNameWebUrl = Doctrine_Core::getTable('God_Model_ModelNameWebURL')->create(array(
                        'model_name_id' => $modelName->ID,
                        'webUrl_id'     => $this->id
                ));
                _d($modelNameWebUrl);
                $modelNameWebUrl->save();
            }
        }
    }

}